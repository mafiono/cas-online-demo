	console.clear();
	// Vars
	const sessions = require('./helpers/sessions'),
		  enquiryClient = require('./helpers/enquiryClient'),
		  enquiryServer = require('./helpers/enquiryServer'),
		  clients = new Map(),
		  {r, g, b, w, c, m, y, k} = require('./helpers/consoleColors');
	var WebSocketServer = require('ws').Server,
		WebSocketClient = require('ws'),
		socketServer = new WebSocketServer({ port: 8080 }),
		base = 'wss://babylonstk.evo-games.com';

	// Local WebSocket server
	socketServer.on('connection', (socketServerConnection, req) => {
	  var id = sessions.uuid();
	  let params = Object.fromEntries(new URLSearchParams(req.url));
	  var sessionid = params.EVOSESSIONID;
	  var metadata = { id, sessionid };
	  clients.set(socketServerConnection, metadata);
	  console.log(`Url ${req.url}`);
	  const userSession = clients.get(socketServerConnection);
	  var url = base + req.url,
		  socketClient = null,
		  socketClientConnection = true;
	
		// Emulator WebSocket server     
		const connect = (url) => {
			socketClient = new WebSocketClient(base + url);
			socketClient.addEventListener('message', function (message) {
				var data = JSON.parse(message.data);
				console.log(c('WebSocket Client Message'), message.data); 
				let stateClient = enquiryClient.ClientMsg(data, userSession);
				socketServerConnection.send(stateClient);
			});
			
			socketServerConnection.on('message', message => {
				var data = JSON.parse(message);
				console.log(c('WebSocket Server Message, User session: ' + userSession.id), JSON.stringify(data));
				let stateServer = enquiryServer.ServerMsg(data, userSession);
				if(socketClient) {
					if(stateServer.entry === 'server') {
						socketServerConnection.send(stateServer.msg);
					} else if(stateServer.entry === 'client') {
						let timeout = socketClientConnection === true ? 1500 : 0;
						setTimeout(function(){
							socketClient.send(stateServer.msg); 
						}, timeout);
					}
				}
			});
		};
		
		connect(req.url);
	});
	
	socketServer.on("close", () => {
		clients.delete(socketServer);
		if(socketClient) socketClient.close();
	});