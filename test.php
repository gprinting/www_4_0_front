<?php
$bitcoin = "1억5천";
$eth = "천만원";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta property="og:title" content="비트코인 : <?php echo $bitcoin?>">
    <meta property="og:description" content="이더리움 : <?php echo $eth?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        h1 { font-size: 2rem; }
    </style>
</head>
<body>
    <p>현재 비트코인 가격: <span id="btc-price">가져오는 중...</span></p>
    <script>
        const websocket = new WebSocket('wss://pubwss.bithumb.com/pub/ws');

        websocket.onopen = () => {
            console.log('WebSocket connected');
            websocket.send(JSON.stringify({
                type: 'ticker',
                symbols: ['BTC_KRW'],
                tickTypes: ['1H']
            }));
        };

        websocket.onmessage = (event) => {
            const data = JSON.parse(event.data);
            if (data.hasOwnProperty('content') && data.content.tickType === '1H') {
                const btcPrice = data.content.closePrice;
                document.getElementById('btc-price').textContent = btcPrice.toLocaleString() + ' KRW';
            }
        };

        websocket.onclose = () => {
            console.log('WebSocket disconnected');
        };

        websocket.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
    </script>
</body>
</html>