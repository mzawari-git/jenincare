(function () {
    if (window._behavioralLoaded) return;
    window._behavioralLoaded = true;

    var data = {
        mouseMovements: [],
        scrollDepth: 0,
        clickCount: 0,
        keypressCount: 0,
        startTime: Date.now(),
        timeOnPage: 0,
        pageWidth: window.innerWidth,
        pageHeight: window.innerHeight,
        totalDistance: 0,
        lastX: null,
        lastY: null,
        maxSpeed: 0,
        speeds: [],
        scrollEvents: 0,
        clicks: [],
    };

    document.addEventListener('mousemove', function (e) {
        if (data.lastX !== null && data.lastY !== null) {
            var dx = e.clientX - data.lastX;
            var dy = e.clientY - data.lastY;
            var dist = Math.sqrt(dx * dx + dy * dy);
            data.totalDistance += dist;
            var speed = dist / 50;
            data.speeds.push(speed);
            if (speed > data.maxSpeed) data.maxSpeed = speed;
        }
        data.lastX = e.clientX;
        data.lastY = e.clientY;
        data.mouseMovements.push({ x: e.clientX, y: e.clientY, t: Date.now() });
        if (data.mouseMovements.length > 100) data.mouseMovements.shift();
    });

    document.addEventListener('scroll', function () {
        var scrollTop = window.scrollY || document.documentElement.scrollTop;
        var docHeight = document.documentElement.scrollHeight - window.innerHeight;
        data.scrollDepth = docHeight > 0 ? Math.round((scrollTop / docHeight) * 100) : 0;
        data.scrollEvents++;
    });

    document.addEventListener('click', function (e) {
        data.clickCount++;
        data.clicks.push({ x: e.clientX, y: e.clientY, t: Date.now(), target: e.target.tagName });
        if (data.clicks.length > 20) data.clicks.shift();
    });

    document.addEventListener('keydown', function () {
        data.keypressCount++;
    });

    function computeBotScore() {
        var score = 0;

        if (data.scrollDepth === 0 && data.clickCount === 0) score += 20;
        if (data.totalDistance < 50) score += 15;
        if (data.maxSpeed > 5000) score += 20;
        if (data.keypressCount === 0 && data.clickCount > 10) score += 10;
        if (data.mouseMovements.length < 3) score += 15;
        if (data.timeOnPage < 1000) score += 20;

        if (data.speeds.length > 2) {
            var avgSpeed = data.speeds.reduce(function (a, b) { return a + b; }, 0) / data.speeds.length;
            if (avgSpeed > 1000) score += 10;
            if (data.speeds.filter(function (s) { return s < 1; }).length > data.speeds.length * 0.5) score += 10;
        }

        return Math.min(100, Math.max(0, score));
    }

    function sendBehavioralData() {
        data.timeOnPage = Date.now() - data.startTime;
        var botScore = computeBotScore();

        var payload = {
            bot_score: botScore,
            time_on_page: data.timeOnPage,
            scroll_depth: data.scrollDepth,
            click_count: data.clickCount,
            keypress_count: data.keypressCount,
            mouse_distance: Math.round(data.totalDistance),
            max_mouse_speed: Math.round(data.maxSpeed),
            page_width: data.pageWidth,
            page_height: data.pageHeight,
            scroll_events: data.scrollEvents,
            user_agent: navigator.userAgent,
        };

        var bp = window.basePath || (function() {
            var m = window.location.pathname.match(/^(.+)\/(public|PUBLIC)\//);
            return m ? m[1] + '/' + m[2] : '';
        })();
        if (navigator.sendBeacon) {
            navigator.sendBeacon(bp + '/api/track/behavior', JSON.stringify(payload));
        } else {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', bp + '/api/track/behavior', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify(payload));
        }
    }

    window.addEventListener('beforeunload', sendBehavioralData);

    setTimeout(sendBehavioralData, 5000);
})();
