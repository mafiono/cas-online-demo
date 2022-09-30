const Logging = require('./Logging');

// CLIENTS (MSGS FROM API EVO)

/*
	States:
		Undefined => 0
		Balance Updated Global State => 1
		Balance Updated Game State => 2
		Lobby Info State => 3
		Blackjack V3 Table Settings => 4
*/

const ClientMsg = function(msg, userSession) {
	let type = PatternMsgClient(msg);
	let response = ForgeryMsgClient(msg, type, userSession);
	return response;
}

const ForgeryMsgClient = function(msg, type, userSession) {
	let response = msg;
	
	if(type === 1) {
		Logging.LoggingMsg(msg, userSession.sessionid);
		/* let balance = 5000;
		response.args.balance = balance.toFixed(2);
		response.args.balances[0].amount = balance.toFixed(2);
		response.args.currencySymbol = '&'; */
	} else if(type === 2) {
		Logging.LoggingMsg(msg, userSession.sessionid);
		/* let balance = 5050;
		response.args.state.balance = balance.toFixed(2);
		response.args.state.balances[0].amount = balance.toFixed(2); */
	} else if(type === 3) {
		//atm nothing
	} else if(type === 4) {
		/*
		response.args.limits = {
			  "seatLimit": 3,
			  "bj-main": {
				"min": 500,
				"max": 5000
			  },
			  "bj-sidebet-perfectpair": {
				"min": 25,
				"max": 5000
			  },
			  "bj-sidebet-anypair": null,
			  "bj-sidebet-21-3": {
				"min": 25,
				"max": 5000
			  },
			  "bj-bb": {
				"min": 25,
				"max": 1000
			  },
			  "chipAmounts": [
				25,
				125,
				250,
				1000,
				2500,
				10000
			  ],
			  "defaultChip": 3
		};
		*/
	}
	
	let data = JSON.stringify(msg);
	let newdata = data.replace(/₺/gi, '$');
	response = JSON.parse(newdata);
	
	return JSON.stringify(response);
}

const PatternMsgClient = function(msg) {
	if(msg.type === 'balanceUpdated') return 1;
	if(msg.type === 'game.state') return 2;
	if(msg.type === 'lobby.tables') return 3;
	if(msg.type === 'blackjack.v3.tableSettings') return 4;
	else return 0;
}

module.exports ={
	ClientMsg
}