function checkAll(checked) {
    var boxes = document.getElementsByTagName('input');
    var boxesLen = boxes.length
    for (i = 0; i < boxesLen; i++) {
        var e = boxes[i];
        if ((e.type == 'checkbox') && (!e.disabled)) {
            e.checked = checked;
        }
    }
}

function setAllSize(value) {
    var selects = getElementsByClass('setSize', null, 'select');
    var selectsLen = selects.length;
    for (i = 0; i < selectsLen; i++) {
        selects[i].value = value;
    }
}

function setAllCat(value) {
    var selects = getElementsByClass('setCat', null, 'select');
    var selectsLen = selects.length;
    for (i = 0; i < selectsLen; i++) {
        selects[i].value = value;
    }
}

function setAllDonor(value) {
    var selects = getElementsByClass('setDonor', null, 'select');
    var selectsLen = selects.length;
    for (i = 0; i < selectsLen; i++) {
        selects[i].value = value;
    }
}

function setAllAppr(checked) {
    var selects = getElementsByClass('setAppr', null, 'input');
    var selectsLen = selects.length;
    for (i = 0; i < selectsLen; i++) {
        selects[i].checked = checked;
    }
}

function toggleAllExtra() {
    var selects = getElementsByClass('toggleExtra', null, 'div');
    var selectsLen = selects.length;
    for (i = 0; i < selectsLen; i++) {
        toggle(selects[i].id);
    }
}

function toggle(id) {
    var el = document.getElementById(id);
    if (el.style.display != 'none') {
        el.style.display = 'none';
    } else {
        el.style.display = 'block';
    }
}

function getElementsByClass(searchClass, node, tag) {
    var classElements = new Array();
    if (node == null)
        node = document;
    if (tag == null)
        tag = '*';
    var els = node.getElementsByTagName(tag);
    var elsLen = els.length;
    var pattern = new RegExp('(^|\\s)' + searchClass + '(\\s|$)');
    for (i = 0, j = 0; i < elsLen; i++) {
        if (pattern.test(els[i].className)) {
            classElements[j] = els[i];
            j++;
        }
    }
    return classElements;
}

function showRss(feedUrl) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', feedUrl);

    xhr.onreadystatechange = function () {
        const DONE = 4;
        const OK = 200;
        let parser;
        let xmlDoc;
        if (xhr.readyState === DONE) {
            if (xhr.status === OK) {
                var result = "";
                if (window.DOMParser) {
                    parser = new DOMParser();
                    xmlDoc = parser.parseFromString(xhr.responseText, "text/xml");
                } else // Internet Explorer
                {
                    xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
                    xmlDoc.async = false;
                    xmlDoc.loadXML(xhr.responseText);
                }

                const maxCount = xmlDoc.getElementsByTagName("item") ? xmlDoc.getElementsByTagName("item").length : 0;
                if(maxCount === 0) {
                    return;
                }

                const items = xmlDoc.getElementsByTagName("item");

                for (let number = 0; number < Math.min(3, maxCount); ++number) {
                    const template = `
<h4>${items[number].getElementsByTagName("title")[0].childNodes[0].nodeValue}<br />
                <small>${items[number].getElementsByTagName("pubDate")[0].childNodes[0].nodeValue} &bull; <a href="${items[number].getElementsByTagName("link")[0].childNodes[0].nodeValue}" target="_blank">permalink</a></small></h4>
                <blockquote>${items[number].getElementsByTagName("description")[0].childNodes[0].nodeValue}</blockquote>`;
                    result += template;
                }

                document.getElementById("rss-feed").innerHTML = result;
            } else {
                console.log('Error: ' + xhr.status); // An error occurred during the request.
            }
        }
    };

    xhr.send(null);
}