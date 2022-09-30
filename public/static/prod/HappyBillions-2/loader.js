window.SKIN_DIRS = {
    "xmass": {
        root: "xmass",
        res: "xmass/v0.0.1"
    },
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
        id: "HappyBillions (game: v0.0.1, utils: v11.3.0)"
    }, (() => {
        var e;
        try {
            e = window.localStorage
        } catch (n) {
            console.log("LocalStorage is unavailable!")
        }
        var i = window.SKIN_DIRS || {};

        function o(i) {
            var o = (i.ui.skin || "basic").toLocaleLowerCase(),
                n = window.location.search.match(new RegExp("[?&]skin=([^&]*)(&?)", "i")),
                t = n ? n[1] : null;
            if (!e) return t || o;
            var a = `lastApiSkin_${i.cache_id}`,
                r = `userSkin_${i.cache_id}`;
            return t ? e.setItem(r, t) : t = e.getItem(r), e.getItem(a) === o && t ? t : (e.removeItem(r), e.setItem(a, o), o)
        }
        window.initializeCasinoOptions = e => {
            var n = o(e),
                {
                    root: t,
                    res: a = "v0.0.19"
                } = i[n] || i.basic || {};
            e.ui.applied_skin = t, e.resources_root_path = e.resources_path + (t ? `/${t}` : ""), e.resources_path += `/${a}`, e.game_bundle_source = e.resources_path + "/bundle.js", window.__OPTIONS__ = e
        }, window.initializeCasinoOptions(window.__OPTIONS__)
    })()
})();
//# sourceMappingURL=./loader.js.map