// script.js
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CSRF token
    const CSRF_TOKEN = document.querySelector('.csrf-token')?.value;
    
    // XSS Protection Function
    const escapeHtml = (unsafe) => {
        if (typeof unsafe !== 'string') return '';
        return unsafe.replace(/[&<"'>]/g, (match) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[match]));
    };

    // Country Options Generator
    const getCountryOptions = (selectedCountry) => {
        const countries = ['Bangladesh', 'India', 'Malaysia', 'Singapore'];
        return countries.map(country => `
            <option value="${escapeHtml(country)}" 
                ${country === selectedCountry ? 'selected' : ''}>
                ${escapeHtml(country)}
            </option>
        `).join('');
    };

    // Fetch Medicine Data with Error Handling
    const fetchData = () => {
        const tableBody = document.querySelector("#medicineTable tbody");
        if (!tableBody) return;

        tableBody.innerHTML = '<tr><td colspan="7">Loading...</td></tr>';

        fetch(`fetch_data.php?csrf_token=${encodeURIComponent(CSRF_TOKEN)}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response not ok');
                return response.json();
            })
            .then(data => {
                tableBody.innerHTML = '';
                data.forEach(medicine => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td class="name">${escapeHtml(medicine.medicine_name)}</td>
                        <td class="company">${escapeHtml(medicine.company_name)}</td>
                        <td class="price">${escapeHtml(medicine.price)} ${escapeHtml(medicine.currency)}</td>
                        <td class="uses">${escapeHtml(medicine.uses)}</td>
                        <td class="added">${escapeHtml(medicine.created_at)}</td>
                        <td>
                            <select class="country-dropdown" 
                                onchange="fetchRowData(${medicine.medicine_id}, this, this.closest('tr'))">
                                ${getCountryOptions(medicine.country)}
                            </select>
                        </td>
                        <td>
                            <button class="update-btn" 
                                onclick="openUpdateForm(${medicine.medicine_id}, '${medicine.country}')">
                                Update
                            </button>
                            <button class="delete-btn" 
                                onclick="confirmDelete(${medicine.medicine_id}, '${medicine.country}')">
                                Delete
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Fetch error:', error);
                tableBody.innerHTML = '<tr><td colspan="7">Failed to load data</td></tr>';
            });
    };

    // Real-time Search Suggestions
    const showSuggestions = (str) => {
        const suggestions = document.getElementById("suggestions");
        if (!suggestions) return;

        if (!str.trim()) {
            suggestions.innerHTML = '';
            suggestions.style.display = 'none';
            return;
        }

        fetch(`get_suggestions.php?q=${encodeURIComponent(str)}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response not ok');
                return response.text();
            })
            .then(data => {
                suggestions.innerHTML = data;
                suggestions.style.display = 'block';
            })
            .catch(error => {
                console.error('Suggestions error:', error);
                suggestions.style.display = 'none';
            });
    };

    // Dynamic Data Fetching
    window.fetchRowData = (medicineId, dropdown, row) => {
        const country = encodeURIComponent(dropdown.value);
        fetch(`fetch_data.php?country=${country}&medicine_id=${medicineId}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response not ok');
                return response.json();
            })
            .then(data => {
                if (data.length > 0) {
                    const medicine = data[0];
                    row.querySelector(".name").textContent = medicine.medicine_name;
                    row.querySelector(".company").textContent = medicine.company_name;
                    row.querySelector(".price").textContent = `${medicine.price} ${medicine.currency}`;
                    row.querySelector(".uses").textContent = medicine.uses;
                    row.querySelector(".added").textContent = medicine.created_at;
                }
            })
            .catch(error => console.error('Row update error:', error));
    };

    // Secure Delete Function
    window.confirmDelete = (medicineId, country) => {
        if (confirm("Are you sure you want to delete this medicine?")) {
            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('medicine_id', medicineId);
            formData.append('country', country);

            fetch('delete_medicine.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert('Delete failed. Please try again.');
            });
        }
    };

    // Update Form Navigation
    window.openUpdateForm = (medicineId, country) => {
        window.location.href = `update_medicine.php?id=${medicineId}&country=${country}`;
    };

    // Initial Data Load
    fetchData();

    // Search Input Handler
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            showSuggestions(e.target.value);
        });
    }
});
let navbar = document.querySelector('.navbar');

document.querySelector('#menu-btn').onclick = () => {
    navbar.classList.toggle('active');
    searchForm.classList.remove('active');
}

let searchForm = document.querySelector('.search-form');
document.querySelector('#search-btn').onclick = () => {
    searchForm.classList.toggle('active');
    navbar.classList.remove('active');
}

window.onscroll = () => {
    navbar.classList.remove('active');
    searchForm.classList.remove('active');
}

