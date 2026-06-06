<?php

namespace MauticPlugin\MauticAIAssistantBundle\EventListener;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Twig\Helper\AssetsHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AssetsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetsHelper $assetsHelper
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_ASSETS => ['injectAIWidget', 0],
        ];
    }

    public function injectAIWidget(): void
    {
        $this->assetsHelper->addCustomDeclaration($this->getWidgetCode());
    }

    private function getWidgetCode(): string
    {
        return <<<'WIDGET'
<script>
(function () {
  var CSS = [
    '#gelab-btn{position:fixed;bottom:28px;right:28px;width:56px;height:56px;background:#1a73e8;border-radius:50%;cursor:pointer;box-shadow:0 4px 16px rgba(26,115,232,.45);display:flex;align-items:center;justify-content:center;z-index:99999;border:none;outline:none;transition:transform .2s,box-shadow .2s}',
    '#gelab-btn:hover{transform:scale(1.08);box-shadow:0 6px 20px rgba(26,115,232,.55)}',
    '#gelab-panel{position:fixed;bottom:96px;right:28px;width:340px;height:480px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,.18);z-index:99998;display:none;flex-direction:column;overflow:hidden;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;font-size:14px}',
    '#gelab-panel.open{display:flex}',
    '#gelab-hd{background:#1a73e8;color:#fff;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}',
    '#gelab-hd .t{font-weight:600;font-size:15px}',
    '#gelab-hd .s{font-size:11px;opacity:.8;margin-top:2px}',
    '#gelab-x{background:none;border:none;color:#fff;cursor:pointer;font-size:22px;padding:0;line-height:1;opacity:.8}',
    '#gelab-x:hover{opacity:1}',
    '#gelab-msgs{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px}',
    '.gm{max-width:85%;padding:9px 12px;border-radius:12px;line-height:1.55;word-break:break-word;white-space:pre-wrap;font-size:13px}',
    '.gm.u{background:#1a73e8;color:#fff;align-self:flex-end;border-bottom-right-radius:3px}',
    '.gm.a{background:#f1f3f4;color:#202124;align-self:flex-start;border-bottom-left-radius:3px}',
    '.gm.tp{background:#f1f3f4;color:#80868b;align-self:flex-start;font-style:italic}',
    '.gm.w{align-self:center;color:#80868b;font-size:12px;background:none;padding:4px 0}',
    '#gelab-foot{padding:10px 12px;border-top:1px solid #e8eaed;display:flex;gap:8px;flex-shrink:0;background:#fff}',
    '#gelab-inp{flex:1;border:1px solid #dadce0;border-radius:8px;padding:8px 12px;font-size:13px;resize:none;outline:none;font-family:inherit;line-height:1.4;max-height:80px;overflow-y:auto}',
    '#gelab-inp:focus{border-color:#1a73e8;box-shadow:0 0 0 2px rgba(26,115,232,.15)}',
    '#gelab-snd{background:#1a73e8;color:#fff;border:none;border-radius:8px;padding:8px 14px;cursor:pointer;font-size:13px;font-weight:500;flex-shrink:0;transition:background .15s}',
    '#gelab-snd:hover{background:#1557b0}',
    '#gelab-snd:disabled{background:#aac4f0;cursor:not-allowed}'
  ].join('');

  var msgs = [];
  var busy = false;

  function init() {
    var st = document.createElement('style');
    st.textContent = CSS;
    document.head.appendChild(st);

    var btn = document.createElement('button');
    btn.id = 'gelab-btn';
    btn.title = 'AI 助手';
    btn.innerHTML = '<svg viewBox="0 0 24 24" width="28" height="28" fill="#fff"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>';

    var panel = document.createElement('div');
    panel.id = 'gelab-panel';
    panel.innerHTML =
      '<div id="gelab-hd">' +
        '<div><div class="t">✨ AI 助手</div><div class="s">外贸营销自动化平台</div></div>' +
        '<button id="gelab-x" title="关闭">×</button>' +
      '</div>' +
      '<div id="gelab-msgs"><div class="gm w">👋 你好！有什么可以帮你的？</div></div>' +
      '<div id="gelab-foot">' +
        '<textarea id="gelab-inp" placeholder="输入问题，Enter 发送..." rows="1"></textarea>' +
        '<button id="gelab-snd">发送</button>' +
      '</div>';

    document.body.appendChild(btn);
    document.body.appendChild(panel);

    var msgsEl = document.getElementById('gelab-msgs');
    var inp    = document.getElementById('gelab-inp');
    var snd    = document.getElementById('gelab-snd');

    btn.addEventListener('click', function () {
      panel.classList.toggle('open');
      if (panel.classList.contains('open')) { inp.focus(); }
    });

    document.getElementById('gelab-x').addEventListener('click', function () {
      panel.classList.remove('open');
    });

    snd.addEventListener('click', send);

    inp.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
    });

    inp.addEventListener('input', function () {
      this.style.height = 'auto';
      this.style.height = Math.min(this.scrollHeight, 80) + 'px';
    });

    function addMsg(cls, text) {
      var d = document.createElement('div');
      d.className = 'gm ' + cls;
      d.textContent = text;
      msgsEl.appendChild(d);
      msgsEl.scrollTop = msgsEl.scrollHeight;
      return d;
    }

    function send() {
      var text = inp.value.trim();
      if (!text || busy) { return; }
      inp.value = '';
      inp.style.height = 'auto';
      addMsg('u', text);
      msgs.push({ role: 'user', content: text });
      busy = true;
      snd.disabled = true;
      inp.disabled = true;
      snd.textContent = '...';
      var tip = addMsg('tp', '正在思考中...');

      fetch('/s/ai-assistant/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ messages: msgs })
      })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        tip.remove();
        var reply = (d.choices && d.choices[0] && d.choices[0].message)
          ? d.choices[0].message.content
          : '抱歉，出现了问题，请稍后再试。';
        msgs.push({ role: 'assistant', content: reply });
        addMsg('a', reply);
      })
      .catch(function () {
        tip.remove();
        addMsg('a', '请求失败，请稍后重试。');
      })
      .finally(function () {
        busy         = false;
        snd.disabled = false;
        inp.disabled = false;
        snd.textContent = '发送';
        inp.focus();
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>
WIDGET;
    }
}
