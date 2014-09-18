


/* About: License
   "THE BEER-WARE LICENSE" (Revision 42):
   <lauri@neti.ee> wrote this file. As long as you retain this notice you
   can do whatever you want with this stuff. If we meet some day, and you
   think this stuff is worth it, you can buy me a beer in return.
   Lauri Rooden. 
*/

/* File: storage.js
*/



var Storage = (function(){
	var undef, win = window, doc = win.document, storage, save, elem;

	function init() {
		/*
		localStorage is available in Opera since 10.50.
		*/
		if ("localStorage" in win) return win.localStorage;
		if ("globalStorage" in win) return win.globalStorage[doc.domain];
		if ("addBehavior" in doc.body) {
			elem = doc.createElement("link");
			elem.style.behavior = "url(#default#userData)";
			doc.getElementsByTagName("head")[0].appendChild(elem);
			elem.load("storage");
			save = function() {
				elem.setAttribute("storage", JSON.stringify(storage) );
				elem.save("storage");
			}
			try{
				var data = elem.getAttribute("storage");
			}catch(e){
				var data = "{}"
			}
			return JSON.parse( data ) || {};
		}
		return {};
	}

	function Storage(name) {
		var value = ( storage || ( storage = init() ) )[name];
		return (name === undef ? storage : ( value === undef ? false : value ) );
	}
	Storage.set = function (name, value, expires /* in seconds*/, path, domain, secure) {
		if (!storage) storage = init();
		storage[name] = value;
		save && save();
	}
	Storage.destroy = function(name) {
		delete storage[name];
		save && save();
	}
	return Storage;
})();


