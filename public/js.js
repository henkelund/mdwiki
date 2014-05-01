(function (exports, doc) {

    exports.Search = function (inputId, resultId, suggestId)
    {
        this._input = doc.getElementById(inputId);
        this._result = doc.getElementById(resultId);
        this._suggest = doc.getElementById(suggestId);
        this._input.onkeyup = this._onKeyUp.bind(this);
        this._lastSearch = 0;
        this._request = new XMLHttpRequest();
        this._request.onreadystatechange = this._responseCallback.bind(this);
    };
    exports.Search.prototype = {

        _onKeyUp: function ()
        {
            var query = this._input.value
            ,   now = new Date().getTime()
            ;
            if (query.length < 3 || (now - this._lastSearch) < 500) {
                return;
            }
            this._lastSearch = now;
            this._request.abort();
            this._request.open('GET', '/?q=' + encodeURIComponent(query), true);
            this._request.send();
        },

        _responseCallback: function ()
        {
            if (this._request.readyState != 4 /* 4 = DONE */
                || this._request.status != 200) {
                return;
            }
            this._result.innerHTML = this._request.responseText;
            this._lastSearch = 0;
        }

    };

    exports.Menu = function ()
    {
        this._toggleElement = doc.getElementById('navLink');
        this._toggleElement.onclick = this._toggleMenu.bind(this);
        this._expandElements = doc.getElementsByClassName('icon-angle-down');
        this.initialize();
    };

    exports.Menu.prototype = {

        initialize: function() {
            // Bind onclick:
            for (var i = 0; i < this._expandElements.length; i++) {
                this._expandElements[i].onclick = this._onClick.bind(this);
            }
        },

        _toggleMenu: function() {
            var body = doc.body,
                state = doc.body.getAttribute('state');

            if (state == 'menu' ) {
               body.setAttribute('state','');
            } else {
              body.setAttribute('state','menu');
            }
        },

        _onClick: function()
        {
            event.preventDefault();
            this._toggleLink(event.toElement.parentNode);
        },

        _toggleLink: function(link){
            var child = link.parentNode.getElementsByTagName('ul')[0];
            if (child.style.display == 'block') {
                child.style.display = 'none';
            } else {
                child.style.display = 'block';
            }
        }
    };

})(window, document);

