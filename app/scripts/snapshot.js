var system = require('system');
var page = require('webpage').create();
var address ='';
if (system.args.length < 3) {
	console.log('Requires a tartget to scan and file name');
    phantom.exit();
} 
else {
	filepath="/opt/hector/app/screenshots/"+system.args[2]
	address=system.args[1];
	page.open(address, function(status){
		if(status !='success'){
			console.log('Status:  ' + status);
			phantom.exit();
		}
		else{
			console.log('Status:  ' + status);
			page.render(filepath);
			phantom.exit();
		}
	});
}