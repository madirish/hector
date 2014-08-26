/**
 * Requires hector.analytics.js
 */

$(document).ready(function () {
	hectorDrawDoughnutChart("top-dst","dstpercent");
	hectorDrawDoughnutChart("top-country","countrypercent");
	hectorDrawDoughnutChart("top-ip","ippercent");
	hectorDrawDoughnutChart("top-proto","protopercent");

});
