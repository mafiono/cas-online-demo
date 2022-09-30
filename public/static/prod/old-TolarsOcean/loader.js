window.SKIN_DIRS = {
    "basic": {
        root: "basic",
        res: "basic/v0.0.1"
    }
};
(() => {
    var e = {};
    e.g = function() {
        if ("object" == typeof globalThis) return globalThis;
        try {
            return this || new Function("return this")()
        } catch (e) {
            if ("object" == typeof window) return window
        }
    }();
    ("undefined" != typeof window ? window : void 0 !== e.g ? e.g : "undefined" != typeof self ? self : {}).SENTRY_RELEASE = {
        id: "FruitMillion (game: v16.0.31, utils: v12.3.0)"
    }, (() => {
        var e;
        try {
            e = window.localStorage
        } catch (t) {
            console.log("LocalStorage is unavailable!")
        }
        var i = window.SKIN_DIRS || {};

        function o(i) {
            var o = (i.ui.skin || "basic").toLocaleLowerCase(),
                t = window.location.search.match(new RegExp("[?&]skin=([^&]*)(&?)", "i")),
                n = t ? t[1] : null;
            if (!e) return n || o;
            var r = `lastApiSkin_${i.cache_id}`,
                a = `userSkin_${i.cache_id}`;
            return n ? e.setItem(a, n) : n = e.getItem(a), e.getItem(r) === o && n ? n : (e.removeItem(a), e.setItem(r, o), o)
        }
        window.initializeCasinoOptions = e => {
            var t = o(e),
                {
                    root: n,
                    res: r = "v16.0.31"
                } = i[t] || i.basic || {};
            e.ui.applied_skin = n, e.resources_root_path = e.resources_path + (n ? `/${n}` : ""), e.resources_path += `/${r}`, e.game_bundle_source = e.resources_path + "/bundle.js", window.__OPTIONS__ = e
        }, window.initializeCasinoOptions(window.__OPTIONS__)
    })()
})();
//# sourceMappingURL=./loader.js.map