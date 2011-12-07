function onPageLoad() {
	updateCode();
} 

function popPreview(baseUrl) {
	var url = buildViewUrl(baseUrl);
	window.open(url);
}

function updateCode(baseUrl) {
	var url = buildViewUrl(baseUrl);
	var box = document.getElementById("viewUrl");
	box.value = url;
	var button = "<a target=\"_blank\" href=\"" + url + "\">[view framed]</a>";
	var box = document.getElementById("viewButton");
	box.value = button;
}

function buildViewUrl(framrBaseUrl) {
	var url = framrBaseUrl + "?cmd=view";
	var form = document.getElementById("viewerOpts");
	url += "&bg=" + form.bg.value;
	url += "&fg=" + form.fg.value;
	url += "&size=" + form.size.value;
	if (form.photo_page.value.length > 0) {
		url += "&photo_page=" + form.photo_page.value;
	}
	if (form.photo_id.value.length > 0) {
		url += "&photo_id=" + form.photo_id.value;
	}
	return url;
}
