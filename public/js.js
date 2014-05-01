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
            if (query.replace(/(^\s)|(\s$)/g, '').length == 0) {
                this._result.innerHTML = '';
                return;
            }
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
        this._expandElements = doc.getElementsByClassName('menu-expand');
        this._storageObject = localStorage.getObject('menu') || {};
        this.initialize();
    };

    exports.Menu.prototype = {

        initialize: function() {
            // Bind onclick:
            for (var i = 0; i < this._expandElements.length; i++) {
                this._expandElements[i].onclick = this._onClick.bind(this);
            }
            // Retrieve values from localStorage:
            var storage = this._storageObject;
            if (typeof storage === 'object'){
                //Set Menu state:
                if(storage.state !== undefined) {
                    doc.body.setAttribute('state', storage.state);
                }
                // Set active nodes:
                if(storage.activeNodes !== undefined) {
                    for (var i = 0; i < storage.activeNodes.length; i++) {
                        var node = doc.getElementById(storage.activeNodes[i]);
                        if (node !== 'undefined') {
                            node.classList.add('active');
                        }
                    }
                }
            }
        },

        _toggleMenu: function(event) {
            var body = doc.body,
                state = doc.body.getAttribute('state');
            event.preventDefault();
            event.stopPropagation();
            if (state == 'menu' ) {
                body.setAttribute('state','');
                this._storageObject.state = '';
            } else {
                body.setAttribute('state','menu');
                this._storageObject.state = 'menu';
            }
            this._store();
        },

        _onClick: function(event) {
            var element = event.currentTarget;
            event.preventDefault();
            event.stopPropagation();
            this._toggleLink(element.parentNode, element);
        },

        _toggleLink: function(link) {
            if (link.parentNode.classList.contains('active')) {
                link.parentNode.classList.remove('active');
            } else {
                link.parentNode.classList.add('active');
            }
            this._findActiveNodes();
        },

        _findActiveNodes: function() {
            var activeNodes = doc.getElementsByTagName('nav')[0].getElementsByClassName('active'),
                activeNodesIds = [];
            for (var i = 0; i < activeNodes.length; i++) {
                activeNodesIds[i] = activeNodes[i].getAttribute('id');
            }
            this._storageObject.activeNodes = activeNodesIds;
            this._store();
        },

        _store: function() {
            localStorage.setObject('menu', this._storageObject)
        }
    };



    exports.App = function ()
    {
        this._links = doc.getElementsByTagName('a');
        this.initialize();
    };

    exports.App.prototype = {

        initialize: function() {
            for (var i = 0; i < this._links.length; i++) {
                this._links[i].onclick = this._onClick.bind(this);
            }
        },

        _onClick: function(event) {
            doc.body.classList.add('loading');
        }

    };



    // Extending localStorage to store objects:
    Storage.prototype.setObject = function(key, value) {
        this.setItem(key, JSON.stringify(value));
    };

    Storage.prototype.getObject = function(key) {
        var value = this.getItem(key);
        return value && JSON.parse(value);
    };

})(window, document);

