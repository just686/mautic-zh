<?php

namespace MauticPlugin\MauticRegistrationBundle\Controller;

use Mautic\UserBundle\Entity\Role;
use Mautic\UserBundle\Entity\User;
use Mautic\UserBundle\Model\PasswordStrengthEstimatorModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly PasswordStrengthEstimatorModel $passwordStrengthModel,
    ) {
    }

    public function registerAction(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('mautic_dashboard_index');
        }

        $errors = [];
        $email  = '';

        if ($request->isMethod('POST')) {
            $email    = trim($request->request->get('email', ''));
            $password = $request->request->get('password', '');
            $confirm  = $request->request->get('confirm_password', '');

            $errors = $this->validate($email, $password, $confirm);

            if (empty($errors)) {
                try {
                    $this->createUser($email, $password);
                    $request->getSession()->set('_registration_success', true);
                    return $this->redirect('/s/login');
                } catch (\Exception $e) {
                    $errors[] = '注册失败，请稍后重试。 / Registration failed, please try again.';
                }
            }
        }

        return $this->render(
            '@MauticRegistration/Registration/register.html.twig',
            [
                'errors' => $errors,
                'email'  => $email,
            ]
        );
    }

    private function validate(string $email, string $password, string $confirm): array
    {
        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = '请输入有效的邮箱地址。 / Please enter a valid email address.';
        }

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $existing = $this->em->getRepository(User::class)
                ->findOneBy(['email' => $email]);
            if ($existing) {
                $errors[] = '该邮箱已被注册。 / This email is already registered.';
            }
        }

        if (strlen($password) < 6) {
            $errors[] = '密码至少需要6位。 / Password must be at least 6 characters.';
            return $errors;
        }

        if ($password !== $confirm) {
            $errors[] = '两次密码不一致。 / Passwords do not match.';
            return $errors;
        }

        // 接入 Mautic 核心 zxcvbn 密码强度校验（score >= 3）
        if (!$this->passwordStrengthModel->validate($password)) {
            $errors[] = '密码强度不足，请使用更复杂的密码（建议混合大小写字母、数字和符号）。 / Password is too weak, please use a stronger password (mix of letters, numbers and symbols recommended).';
        }

        return $errors;
    }

    private function createUser(string $email, string $password): void
    {
        $role = $this->em->getRepository(Role::class)
            ->findOneBy(['name' => 'HaiKe User']);

        if (!$role) {
            $role = new Role();
            $role->setName('HaiKe User');
            $role->setDescription('海客平台普通用户');
            $role->setIsAdmin(false);
            $this->em->persist($role);
            $this->em->flush();

            $this->setRolePermissions($role);
        }

        $username = explode('@', $email)[0] . '_' . substr(md5($email), 0, 6);

        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setFirstName(explode('@', $email)[0]);
        $user->setLastName('');
        $user->setRole($role);
        $user->setTimezone('Asia/Shanghai');
        $user->setLocale('zh_CN');

        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();
    }

    private function setRolePermissions(Role $role): void
    {
        $permissionData = [
            'lead:leads'         => ['viewown', 'create', 'editown', 'deleteown'],
            'lead:lists'         => ['viewown', 'create', 'editown', 'deleteown'],
            'email:emails'       => ['viewown', 'create', 'editown', 'deleteown'],
            'campaign:campaigns' => ['viewown', 'create', 'editown', 'deleteown'],
            'form:forms'         => ['viewown', 'create', 'editown', 'deleteown'],
            'page:pages'         => ['viewown', 'create', 'editown', 'deleteown'],
            'asset:assets'       => ['viewown', 'create', 'editown', 'deleteown'],
            'report:reports'     => ['viewown', 'create'],
            'point:points'       => ['viewown', 'create', 'editown', 'deleteown'],
            'stage:stages'       => ['viewown', 'create', 'editown', 'deleteown'],
            'category:items'     => ['viewown', 'create', 'editown', 'deleteown'],
        ];

        $conn = $this->em->getConnection();

        foreach ($permissionData as $bundle => $levels) {
            [$bundleName, $name] = explode(':', $bundle);
            $bitwise  = 0;
            $levelMap = [
                'viewown'     => 2,
                'viewother'   => 4,
                'create'      => 32,
                'editown'     => 8,
                'editother'   => 16,
                'deleteown'   => 64,
                'deleteother' => 128,
                'publishown'  => 256,
                'publishother'=> 512,
            ];
            foreach ($levels as $level) {
                $bitwise |= ($levelMap[$level] ?? 0);
            }
            $conn->executeStatement(
                'INSERT INTO permissions (role_id, bundle, name, bitwise)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE bitwise = ?',
                [$role->getId(), $bundleName, $name, $bitwise, $bitwise]
            );
        }
    }
}
