<?php

namespace MauticPlugin\MauticRegistrationBundle\EventListener;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Twig\Helper\AssetsHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoginPageListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetsHelper $assetsHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_ASSETS => ['injectRegisterLink', 0],
        ];
    }

    public function injectRegisterLink(): void
    {
        $this->assetsHelper->addCustomDeclaration(<<<'HTML'
<script>
(function(){
  function addRegisterLink(){
    var form = document.querySelector('form[action*="login"], form[name="login"]');
    if(!form){ return; }
    if(document.getElementById('haike-register-link')){ return; }
    var wrap = document.createElement('div');
    wrap.id = 'haike-register-link';
    wrap.style.cssText = 'text-align:center;margin-top:16px;font-size:13px;color:#666;';
    wrap.innerHTML = '还没有账号？<a href="/register" style="color:#4a6cf7;font-weight:500;"> 立即注册</a>';
    form.parentNode.insertBefore(wrap, form.nextSibling);
  }
  if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded', addRegisterLink);
  } else {
    addRegisterLink();
  }
})();
</script>
HTML);

        $this->assetsHelper->addCustomDeclaration(
            '<script>
    document.addEventListener("DOMContentLoaded", function() {
        var usernameInput = document.getElementById("username");
        var passwordInput = document.getElementById("password");

        // 填充邮箱（来自 meta 标签，服务端 session 传递）
        var emailMeta = document.querySelector("meta[name=\'registration-email\']");
        if (emailMeta && emailMeta.content && usernameInput) {
            usernameInput.value = emailMeta.content;
        }

        // 填充密码（来自 sessionStorage，纯前端临时存储）
        var regPwd = sessionStorage.getItem("_reg_pwd");
        if (regPwd && passwordInput) {
            passwordInput.value = regPwd;
            sessionStorage.removeItem("_reg_pwd");
        }

        // 邮箱和密码都填好后，聚焦到登录按钮
        if (emailMeta && emailMeta.content && regPwd) {
            var loginBtn = document.querySelector("button[type=\'submit\']");
            if (loginBtn) loginBtn.focus();
        }
    });
    </script>'
        );
    }
}
