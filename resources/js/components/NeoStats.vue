<template>
    <div class="neo-stats">
        <h1>Neo Stats</h1>
        <form @submit.prevent="validateAndFetch">
            <label for="start_date">Start Date:</label>
            <Datepicker v-model="startDate" :class="'date-input'" required />

            <label for="end_date">End Date:</label>
            <Datepicker v-model="endDate" :class="'date-input'" required />

            <button type="submit" class="submit-button" :disabled="loading">
                {{ loading ? 'Please wait...' : 'Submit' }}
            </button>
        </form>

        <!-- Error Message -->
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

        <div v-if="stats">
            <h2>Results:</h2>
            <p>Fastest Asteroid: Id: {{ stats.fastest_asteroid.id }} Name: {{ stats.fastest_asteroid.name }} at {{ stats.fastest_asteroid.speed }} km/h</p>
            <p>Closest Asteroid: Id: {{ stats.closest_asteroid.id }} Name: {{ stats.closest_asteroid.name }} at {{ stats.closest_asteroid.distance }} km</p>
            <p>Average Size of Asteroids: {{ stats.average_size }} km</p>
        </div>
        <canvas id="neoChart"></canvas>
    </div>
</template>

<script>
import axios from 'axios';
import { Chart } from 'chart.js/auto';
import Datepicker from 'vue3-datepicker'; // Import the datepicker

export default {
    components: {
        Datepicker // Register the datepicker component
    },
    data() {
        return {
            startDate: '',
            endDate: '',
            stats: null,
            errorMessage: '', // Error message state
            loading: false // Track the loading state
        };
    },
    methods: {
        validateAndFetch() {
            // Clear previous error message
            this.errorMessage = '';

            // Validation
            if (!this.startDate || !this.endDate) {
                this.errorMessage = 'Please select both start and end dates.';
                return;
            }

            // Proceed with fetching stats if validation passes
            this.fetchNeoStats();
        },
        async fetchNeoStats() {
            this.loading = true; // Set loading to true when the request starts
            try {
                const response = await axios.post('/fetch-neostats', {
                    start_date: this.startDate,
                    end_date: this.endDate
                  }).then(response => {
                    // Handle success response here
                    this.errorMessage = ''; // Clear error message on success
                    this.stats = response.data;
                    this.renderChart(this.stats.daily_asteroids);
                  })
                  .catch(error => {
                    // Handle error response here
                    if (error.response) {
                        // Server responded with a status code outside the 2xx range
                        this.errorMessage = `Error: ${error.response.status} - ${error.response.data.error || 'Something went wrong.'}`;
                    } else if (error.request) {
                        // Request was made but no response received
                        this.errorMessage = 'No response received from the server.';
                    } else {
                        // Something else happened
                        this.errorMessage = `Error: ${error.message}`;
                    }
                  });
            } catch (error) {
                console.error(error.response);
            } finally {
                this.loading = false; // Set loading to false when the request is finished
            }
        },
        renderChart(data) {
            const ctx = document.getElementById('neoChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',  // You can also use 'line' for a line chart
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        label: 'Number of Asteroids',
                        data: Object.values(data),
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }
};
</script>