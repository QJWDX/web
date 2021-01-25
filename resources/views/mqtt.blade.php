<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>web MQTT WebSockets Example</title>
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <link href="https://cdn.bootcss.com/twitter-bootstrap/2.3.2/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/twitter-bootstrap/2.3.2/css/bootstrap-responsive.css" rel="stylesheet">
    <style type="text/css">
        body { padding-top: 40px; }
    </style>
</head>
<body>
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="#">web MQTT WebSockets</a>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div id="connect">
                <div class="page-header">
                    <h2>Server Login</h2>
                </div>
                <form class="form-horizontal" id='connect_form'>
                    <fieldset>
                        <div class="control-group">
                            <label>WebSocket URL</label>
                            <div class="controls">
                                <input name=url id='ws_hostname' value='127.0.0.1' type="text">
                            </div>
                        </div>
                        <div class="control-group">
                            <label>Port</label>
                            <div class="controls">
                                <input name=url id='ws_port' value='15675' type="text">
                            </div>
                        </div>
                        <div class="control-group">
                            <label>User</label>
                            <div class="controls">
                                <input id='connect_login' placeholder="User Login" value="admin" type="text">
                            </div>
                        </div>
                        <div class="control-group">
                            <label>Password</label>
                            <div class="controls">
                                <input id='connect_passcode' placeholder="User Password" value="admin123" type="password">
                            </div>
                        </div>
                        <div class="control-group">
                            <label>Destination</label>
                            <div class="controls">
                                <input id='destination' placeholder="Destination" value="/topic/logs" type="text">
                            </div>
                        </div>
                        <div class="control-group">
                            <label>Text</label>
                            <div class="controls">
                                <input id='text' placeholder="Text" value="Hello, World!" type="text">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button id='connect_submit' type="submit" class="btn btn-large btn-primary">Send</button>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div id="messages">
                <h2>Messages</h2>
            </div>
        </div>
    </div>
</div>

<!-- Scripts placed at the end of the document so the pages load faster -->
<script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        if(window.WebSocket) {
            $('#connect_form').submit(function() {
                var host_name = $("#ws_hostname").val();
                var port = $("#ws_port").val();
                var login = $("#connect_login").val();
                var passcode = $("#connect_passcode").val();
                var destination = $("#destination").val();
                var text = $("#text").val();
                // Create a client instance
                var client = new Paho.MQTT.Client(
                    host_name,
                    Number(port),
                    "/ws",
                    "myclientid_" + parseInt(Math.random() * 100, 10));
                // set callback handlers
                client.onConnectionLost = onConnectionLost;
                client.onMessageArrived = onMessageArrived;

                // connect the client
                var options = {
                    onSuccess:onSuccess,
                    onFailure:onFailure,
                    keepAliveInterval:10,
                    timeout:3
                };

                if (location.protocol == "https:") {
                    options.useSSL = true;
                }

                client.connect(options);
                // called when the client connects
                function onSuccess() {
                    // Once a connection has been made, make a subscription and send a message.
                    console.log("onConnect");
                    client.subscribe("notification",{qos: 1});
                    message = new Paho.MQTT.Message("Hello");
                    message.destinationName = "World";
                    client.send(message);
                }

                function onFailure(message) {
                    console.log("CONNECTION FAILURE - " + message.errorMessage);
                }

                // called when the client loses its connection
                function onConnectionLost(responseObject) {
                    if (responseObject.errorCode !== 0) {
                        console.log("onConnectionLost:"+responseObject.errorMessage);
                    }
                }

                // called when a message arrives
                function onMessageArrived(message) {
                    console.log("onMessageArrived:"+message.payloadString);
                }

                return false;
            });
        } else {
            $("#connect").html("\
            <h1>Get a new Web Browser!</h1>\
            <p>\
            Your browser does not support WebSockets. This example will not work properly.<br>\
            Please use a Web Browser with WebSockets support (WebKit or Google Chrome).\
            </p>\
        ");
        }
    });</script>
</body>
</html>
