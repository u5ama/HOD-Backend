window.onload = function() {
    if(typeof jQuery=='undefined') {
        const script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.4.1.min.js';
        script.type = 'text/javascript';
        document.getElementsByTagName('head')[0].appendChild(script);
    }

   //  const iframeTag = document.createElement('script');
   //  iframeTag.type = 'text/javascript';
   //  iframeTag.language = 'javascript';
   //  iframeTag.innerHTML = 'function resizeIframe(obj){ obj.style.height = obj.contentWindow.document.body.scrollHeight + \'px\';}';
   // // document.getElementsByTagName('head')[0].appendChild(iframeTag);
   //  document.body.appendChild(iframeTag);

    const div = document.createElement('div');
    div.setAttribute('id', 'ifrm_marker'); // assign an id
    document.body.appendChild(div);

    const ifrm = document.createElement('iframe');
    ifrm.setAttribute('id', 'ifrm'); // assign an id
    ifrm.setAttribute('width', '100%');
    ifrm.setAttribute('allowtransparency', 'true');
    ifrm.setAttribute('allow', 'geolocation; microphone; camera');
    ifrm.setAttribute('allowfullscreen', 'true');
    ifrm.setAttribute('style', 'width: 10px; min-width: 100%; display: block; overflow: hidden; border: none;width: 100%; height: 1500px !important;');
    ifrm.setAttribute('scrolling', 'auto');
    ifrm.setAttribute('onload', 'javascript:(function(o){o.style.height=o.contentWindow.document.body.scrollHeight+"px";}(this));');
   // document.body.appendChild(ifrm); // to place at end of document

// to place before another page element
    var el = document.getElementById('ifrm_marker');
    el.append(ifrm);
    const scripts = document.getElementsByTagName('script');
    const myScript = scripts[ scripts.length - 1 ];

    const queryString = myScript.src.replace(/^[^\?]+\??/,'');

    const params = parseQuery( queryString );
    const user_id = params.dv;
    // console.log(user_id);
// assign url
    ifrm.setAttribute('src', 'https://staging-api.heroesofdigital.io/appointmentPage?user_id='+ user_id);
 // ifrm.setAttribute('src', 'http://localhost/hod_backend/appointmentPage?user_id='+ user_id);
};

function parseQuery ( query ) {
    const Params = new Object ();
    if ( ! query ) return Params; // return empty object
    const Pairs = query.split(/[;&]/);
    for ( var i = 0; i < Pairs.length; i++ ) {
        var KeyVal = Pairs[i].split('=');
        if ( ! KeyVal || KeyVal.length != 2 ) continue;
        var key = unescape( KeyVal[0] );
        var val = unescape( KeyVal[1] );
        val = val.replace(/\+/g, ' ');
        Params[key] = val;
    }
    return Params;
}

