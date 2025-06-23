function updateParentInfo() {
    fetch('api/get_distance_parent.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('parent-info-container');
            container.innerHTML = '';
            
            if (data.error) {
                container.innerHTML = '<p class="no-parent">Error loading data</p>';
                return;
            }
            
            if (data.children && data.children.length > 0) {
                data.children.forEach(child => {
                    const childDiv = document.createElement('div');
                    childDiv.className = 'child-parent-info';
                    
                    const childName = document.createElement('div');
                    childName.className = 'child-name';
                    childName.textContent = `${child.first_name} ${child.last_name}`;
                    childDiv.appendChild(childName);
                    
                    if (child.distance_parent) {
                        const distance = document.createElement('div');
                        distance.className = 'parent-distance';
                        distance.textContent = `Distance: ${child.distance_parent} units`;
                        childDiv.appendChild(distance);

                        const description = document.createElement('div');
                        description.className = 'parent-description';
                        const distanceValue = parseFloat(child.distance_parent)
                        if (distanceValue < 200) {
                            description.textContent = 'SAFE';
                            description.style.color = 'green';
                            description.style.fontWeight = 'bold';
                            description.style.fontFamily = "Helvetica Neue";
                        } else {
                            description.textContent = 'DANGER ZONE';
                            description.style.color = 'red';
                            description.style.fontWeight = 'bold';
                            description.style.fontFamily = "Helvetica Neue";
                        }
                        childDiv.appendChild(description);
                    } else {
                        const noData = document.createElement('div');
                        noData.className = 'no-parent';
                        noData.textContent = 'Error: no existing data';
                        childDiv.appendChild(noData);
                    }
                    
                    container.appendChild(childDiv);
                });
            } else {
                container.innerHTML = '<p class="no-parent">No registered children</p>';
            }
            
            const now = new Date();
            document.getElementById('parent-last-update').textContent = 
                `Last update: ${now.toLocaleTimeString('ro-RO')}`;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('parent-info-container').innerHTML = 
                '<p class="no-parent">Error: cant connect to the server</p>';
        });
}

updateParentInfo();
setInterval(updateParentInfo, 2000);