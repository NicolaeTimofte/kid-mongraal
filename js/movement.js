class ContinuousMovement {
    constructor(intervalSeconds = 3) { 
        this.intervalSeconds = intervalSeconds * 1000;
        this.isRunning = false;
        this.intervalId = null;
    }
    
    start() {
        if (this.isRunning) return;
        
        this.isRunning = true;
        console.log('Mișcare continuă pornită - interval:', this.intervalSeconds / 1000, 'secunde');
        
        this.performMovement();
        
        this.intervalId = setInterval(() => {
            this.performMovement();
        }, this.intervalSeconds);
    }
    
    stop() {
        if (!this.isRunning) return;
        
        this.isRunning = false;
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        console.log('Mișcare continuă oprită');
    }
    
    performMovement() {
        fetch('movement/movement.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'OK') {
                console.log('Pas executat:', new Date().toLocaleTimeString());
            } else {
                console.error('Eroare la pas:', data);
            }
        })
        .catch(error => {
            console.error('Eroare fetch:', error);
        });
    }
    
    changeInterval(newIntervalSeconds) {
        this.intervalSeconds = newIntervalSeconds * 1000;
        if (this.isRunning) {
            this.stop();
            this.start();
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.continuousMovement = new ContinuousMovement(3);
    window.continuousMovement.start();
});

window.addEventListener('beforeunload', function() {
    if (window.continuousMovement) {
        window.continuousMovement.stop();
    }
});

document.addEventListener('visibilitychange', function() {
    if (window.continuousMovement) {
        if (document.hidden) {
            window.continuousMovement.stop();
        } else {
            window.continuousMovement.start();
        }
    }
});