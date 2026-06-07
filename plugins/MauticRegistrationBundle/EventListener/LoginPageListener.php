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

        $this->assetsHelper->addCustomDeclaration(<<<'HTML'
<script>
(function(){
  var email = document.querySelector('meta[name="registration-email"]');
  if (email && email.content) {
    var usernameInput = document.getElementById('username');
    if (usernameInput) {
      usernameInput.value = email.content;
      var passwordInput = document.getElementById('password');
      if (passwordInput) {
        passwordInput.focus();
      }
    }
  }
})();
</script>
HTML);
    }
}
