let allMesures = []; 
const mesuresParPage = 10; 
let pageActuelle = 1; 

async function fetchMesures() {
  try {
    const response = await fetch('api.php');
    const data = await response.json();

    // Filtrer les données valides
    allMesures = data.filter(m => {
      const date = new Date(m.time);
      return date.toString() !== 'Invalid Date';
    });

    pageActuelle = 1; // On revient à la première page au chargement
    afficherMesures();
    afficherPagination();

  } catch (error) {
    console.error('Erreur lors de la récupération des mesures :', error);
  }
}

function afficherMesures() {
  const tbody = document.querySelector('#mesures tbody');
  
  const debut = (pageActuelle - 1) * mesuresParPage;
  const fin = debut + mesuresParPage;
  const mesuresAffichees = allMesures.slice(debut, fin);

  tbody.innerHTML = mesuresAffichees.map(m => `
    <tr>
      <td>${m.id}</td>
      <td>${m.device}</td>
      <td>${new Date(m.time).toLocaleString()}</td>
      <td>${m.temperature}°C</td>
      <td>${m.humidity}%</td>
    </tr>
  `).join('');

  afficherGraphique(mesuresAffichees);
}

function afficherGraphique(mesures) {
  const ctx = document.getElementById('chart').getContext('2d');
  const labels = mesures.map(m => new Date(m.time).toLocaleTimeString()).reverse();
  const temperatures = mesures.map(m => m.temperature).reverse();
  const humidities = mesures.map(m => m.humidity).reverse();

  if (window.iotChart) {
    window.iotChart.destroy();
  }

  window.iotChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Température (°C)',
          data: temperatures,
          borderColor: '#ff6384',
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          tension: 0.4
        },
        {
          label: 'Humidité (%)',
          data: humidities,
          borderColor: '#36a2eb',
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          tension: 0.4
        }
      ]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}

function afficherPagination() {
  const paginationDiv = document.getElementById('pagination');
  paginationDiv.innerHTML = '';

  const nombrePages = Math.ceil(allMesures.length / mesuresParPage);

  for (let i = 1; i <= nombrePages; i++) {
    const bouton = document.createElement('button');
    bouton.textContent = i;
    bouton.className = (i === pageActuelle) ? 'active' : '';
    bouton.onclick = () => {
      pageActuelle = i;
      afficherMesures();
      afficherPagination();
    };
    paginationDiv.appendChild(bouton);
  }
}

// Charger les mesures au début
fetchMesures();

// Recharger toutes les 60 secondes
setInterval(fetchMesures, 60000);
