(function(){
  var s=document.currentScript; if(!s) return;
  var clientKey=s.getAttribute('data-client-key'); if(!clientKey) return;
  var base=s.src.replace(/\/widget\.js(?:\?.*)?$/,'');
  var css=document.createElement('link'); css.rel='stylesheet'; css.href=base+'/widget.css'; document.head.appendChild(css);
  var visitorId=localStorage.getItem('ai_chatbot_visitor_id');
  if(!visitorId){visitorId=(Math.random().toString(16).slice(2)+Date.now().toString(16)).slice(0,32);localStorage.setItem('ai_chatbot_visitor_id',visitorId);}  
  var convoId=localStorage.getItem('ai_chatbot_conversation_'+clientKey)||'';
  var bubble=document.createElement('div');bubble.className='ai-chatbot-bubble';bubble.textContent='💬';
  var panel=document.createElement('div');panel.className='ai-chatbot-panel';
  panel.innerHTML='<div class="ai-chatbot-head">AI Assistant</div><div class="ai-chatbot-messages"></div><div class="ai-chatbot-input"><input type="text" placeholder="Type a message..."><button>Send</button></div>';
  document.body.appendChild(bubble); document.body.appendChild(panel);
  var msgs=panel.querySelector('.ai-chatbot-messages'), input=panel.querySelector('input'), btn=panel.querySelector('button');
  function add(role,text){var d=document.createElement('div');d.className='ai-msg '+(role==='user'?'ai-user':'ai-assistant');d.textContent=text;msgs.appendChild(d);msgs.scrollTop=msgs.scrollHeight;}
  bubble.onclick=function(){panel.style.display=panel.style.display==='flex'?'none':'flex';};
  function send(){var m=input.value.trim(); if(!m) return; add('user',m); input.value=''; add('assistant','Typing...');
    fetch(base+'/chat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({client_key:clientKey,visitor_id:visitorId,message:m,page_url:location.href,referrer:document.referrer,conversation_id:convoId})})
    .then(r=>r.json()).then(function(res){msgs.removeChild(msgs.lastChild); if(!res.success){add('assistant','Sorry, something went wrong.');return;} convoId=res.conversation_id; localStorage.setItem('ai_chatbot_conversation_'+clientKey,convoId); add('assistant',res.reply);})
    .catch(function(){msgs.removeChild(msgs.lastChild); add('assistant','Network error. Please try again.');});
  }
  btn.onclick=send; input.addEventListener('keydown',function(e){if(e.key==='Enter')send();});
})();
