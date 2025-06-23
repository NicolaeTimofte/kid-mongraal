function updateAccidentInfo() {
    fetch('api/get_nearest_accidents.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('accident-info-container');
            container.innerHTML = '';
            
            if (data.error) {
                container.innerHTML = '<p class="no-accidents">Error loading data</p>';
                return;
            }
            
            if (data.children && data.children.length > 0) {
                data.children.forEach(child => {
                    const childDiv = document.createElement('div');
                    childDiv.className = 'child-accident-info';
                    
                    const childName = document.createElement('div');
                    childName.className = 'child-name';
                    childName.textContent = `${child.first_name} ${child.last_name}`;
                    childDiv.appendChild(childName);
                    
                    if (child.nearest_accident) {
                        const parts = child.nearest_accident.split(' - ');
                        
                        const distance = document.createElement('div');
                        distance.className = 'accident-distance';
                        distance.textContent = `Distance: ${parts[0]} units`;
                        childDiv.appendChild(distance);
                        
                        if (parts[1]) {
                            const description = document.createElement('div');
                            description.className = 'accident-description';
                            description.textContent = parts[1];
                            childDiv.appendChild(description);
                        }
                    } else {
                        const noAccident = document.createElement('div');
                        noAccident.className = 'no-accidents';
                        noAccident.textContent = 'Data doesnt exist';
                        childDiv.appendChild(noAccident);
                    }
                    
                    container.appendChild(childDiv);
                });
            } else {
                container.innerHTML = '<p class="no-accidents">No registered children</p>';
            }
            
            const now = new Date();
            document.getElementById('accident-last-update').textContent = 
                `Last update: ${now.toLocaleTimeString('ro-RO')}`;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('accident-info-container').innerHTML = 
                '<p class="no-accidents">Error: cant connect to server</p>';
        });
}
updateAccidentInfo();
setInterval(updateAccidentInfo, 2000);