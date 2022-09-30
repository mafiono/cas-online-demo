const Logging = require('./Logging'); 

// SERVER (MSGS FROM OUR CLIENT SITE)

/*
	States:
		Undefined => 0
		Chat Sending Text => 1
*/

const ServerMsg = function(msg, userSession) {
	let type = PatternMsgServer(msg);
	let response = ForgeryMsgServer(msg, type, userSession);
	return response;
}

const ForgeryMsgServer = function(msg, type, userSession) {
	let response = msg;
	let entry = 'client';
	if(type === 1) {
		Logging.LoggingMsg(msg, userSession.sessionid);
		entry = 'server';
		response = {
			"id": Date.now()+'-1234',
			"type": "chat.text",
			"args": {
				"messageId": msg.id,
				"text": msg.args.text,
				"source": {
					"type": "player",
					"id": msg.id.substring(0, 16),
					"name": "Me",
					"casino": null,
					"tags": [],
					"warned": false
				},
				"destination": {
					"type": "players",
					"casino": "babylonstk000001",
					"casinos": [
						"babylonstk000001"
					],
					"table": "CrazyTime0000001"
				},
				"mode": "common",
				"time": Date.now()
			},
			"time": Date.now()
		};
	}
	
	return { 
		'entry': entry,
		'msg': JSON.stringify(response)
	};
}

const PatternMsgServer = function(msg) {
	if(msg.type === 'chat.text') return 1;
	else return 0;
}

module.exports ={
	ServerMsg
}