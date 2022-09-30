const fs = require('fs');

const LoggingMsg = function(msg, userSession) {
	/*
	var file = './logs/'+userSession+'_messages.json';
	var msg = JSON.stringify(msg);
	fs.readFile(file, function (err, data) {
		if(err){
			fs.writeFile(file, msg, { flag: 'wx' }, function(err) {})
		} else {
			fs.appendFile(file, msg, function (err) {
			  if (err) throw err;
			});
		}
	})
	*/
}

module.exports ={
	LoggingMsg
}
