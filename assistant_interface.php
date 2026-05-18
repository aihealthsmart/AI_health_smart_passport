<div id="ai-assistant-wrapper" style="position: fixed; bottom: 100px; right: 30px; width: 350px; background: white; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); display: none; flex-direction: column; z-index: 9999;">
    <div style="background: #005a8d; color: white; padding: 15px; border-top-left-radius: 10px; border-top-right-radius: 10px;">
        <strong>AI Health Assistant</strong>
    </div>
    <div id="chat-output" style="height: 300px; overflow-y: auto; padding: 15px; font-size: 0.9rem; background: #fcfcfc;">
        <p>Welcome, <?php echo $_SESSION['username']; ?>. How can I help you with hospital data today?</p>
    </div>
    <div style="padding: 10px; border-top: 1px solid #eee; display: flex;">
        <input type="text" id="chat-query" placeholder="Ask a question..." style="flex: 1; border: 1px solid #ddd; padding: 8px; border-radius: 5px;">
        <button onclick="askAI()" style="background: #28a745; color: white; border: none; padding: 5px 15px; margin-left: 5px; border-radius: 5px; cursor: pointer;">Ask</button>
    </div>
</div>

<button onclick="toggleChat()" style="position: fixed; bottom: 30px; right: 100px; background: #005a8d; color: white; border: none; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; font-size: 24px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">💬</button>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleChat() {
    let chat = document.getElementById('ai-assistant-wrapper');
    chat.style.display = (chat.style.display === 'none' || chat.style.display === '') ? 'flex' : 'none';
}

function askAI() {
    let query = document.getElementById('chat-query').value;
    let output = document.getElementById('chat-output');
    
    if(!query) return;

    output.innerHTML += `<div style="text-align:right; margin-bottom:10px;"><strong>You:</strong> ${query}</div>`;
    document.getElementById('chat-query').value = '';

    fetch('ai_engine.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'query=' + encodeURIComponent(query)
    })
    .then(res => res.json())
    .then(data => {
        if(data.type === 'text') {
            output.innerHTML += `<div style="margin-bottom:10px;"><strong>AI:</strong> ${data.content}</div>`;
        } 
        else if(data.type === 'table') {
            let html = '<table border="1" style="width:100%; border-collapse:collapse; font-size:0.7rem; margin-top:5px;">';
            html += '<tr style="background:#eee;"><th>ID</th><th>Date</th><th>Ward</th></tr>';
            data.content.forEach(row => {
                html += `<tr><td>${row.patient_id}</td><td>${row.admission_date}</td><td>${row.ward}</td></tr>`;
            });
            html += '</table>';
            output.innerHTML += `<div style="margin-bottom:10px;"><strong>AI:</strong> Showing recent admissions:<br>${html}</div>`;
        } 
        else if(data.type === 'chart') {
            let canvasId = 'chart-' + Date.now();
            output.innerHTML += `<div><strong>AI:</strong> Appointment distribution:<canvas id="${canvasId}"></canvas></div>`;
            setTimeout(() => {
                new Chart(document.getElementById(canvasId), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{ label: 'Appointments', data: data.data, backgroundColor: '#005a8d' }]
                    }
                });
            }, 100);
        }
        output.scrollTop = output.scrollHeight;
    });
}
</script>