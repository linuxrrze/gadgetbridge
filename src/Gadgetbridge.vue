<template>
<Content :class="{'icon-loading': loading}" app-name="gadgetbridge">
		<AppNavigation>
			<AppNavigationNew v-if="!loading"
				:text="t('gadgetbridge', 'Select Database')"
				:disabled="false"
				button-id="new-gadgetbridge-button"
				button-class="icon-add"
				@click="filePickDatabase" />
			<AppNavigationItem v-if="databaseFilePath" :title="databaseFilePath" icon="icon-folder">
				<AppNavigationItem title="Test" style="display:none"></AppNavigationItem> <!-- This is stupid, but makes the nesting work.  FIXME -->
				<template v-for="device in devices">
					<li :key="device._id" :class="{ active: (selectedDevice == device) }">
						<a href="#" @click="selectedDevice = device">
							<i class="fa fa-upload"></i>
							<span>{{device.NAME}}</span> <em>{{device.IDENTIFIER}}</em>
						</a>
					</li>
			    	<!-- <AppNavigationItem v-bind:key="device._id" v-bind:title="displayDeviceTitle(device)"></AppNavigationItem> -->
				</template>
            </AppNavigationItem>
		</AppNavigation>
		<AppContent>
			<div id="empty-content" v-if="!this.selectedDevice">
				<div class="icon-activity"></div>
				<h2>No Data found</h2>
				<p>Please Import data from android app to continue</p>
			</div>
			<template v-else>
				<bar-chart :chart-data="chartData" :options="chartOptions" :styles="myStyles" />
			</template>
		</AppContent>
	</Content>
</template>
<script>
import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'

import axios from 'axios'
axios.defaults.headers.post['Accept'] = 'application/json';

import { getFilePickerBuilder } from '@nextcloud/dialogs'

import BarChart from './BarChart.js'
import '@nextcloud/dialogs/styles/toast.scss'

export default {
	name: 'Gadgetbridge',
	components: {
		Content,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNew,
		BarChart,
	},
	props: [ 'dbPath' ],
	data() {
		return {
            databaseFileId: null,
            databaseFilePath: this.dbPath,
            devices: [],
			loading: false,
			selectedDevice: null,
			show: true,
			deviceData: {
				labels: [],
				kinds: [],
				steps: [],
				activityColors: [],
				heartRates: [],
				lastHeartRate: null
			},
			chartData: {},
			chartOptions: {
					legend: {
						display: false
					},
					scales: {
						xAxes: [{
							gridLines: {
								offsetGridLines: true
							},
							stacked: true
						}],
						yAxes: [{
							stacked: true
						}]
					},
					responsive: true,
					elements: {
						line: {
							tension: 0
						}
					}
				}
		}
	},
	watch: {
		selectedDevice: function() {
			this.loadDeviceData(moment().format('YYYY/MM/DD/HH/mm'));
		}
	},
	methods: {
		getActivityColor(current) {
			switch (current) {
				case 1: // Activity
					return '#3ADF00';
				case 2: // Light sleep
					return '#2ECCFA';
				case 4: // Deep sleep
					return '#0040FF';
				case 8: // Not worn
					return '#AAAAAA';
				default:
					return '#AAAAAA';
			}
		},
		getSteps(current, steps) {
			switch (current) {
				case 1: // Activity
				case 2: // Light sleep
				case 4: // Deep sleep
				case 8: // Not worn
					return Math.min(250, Math.max(10, steps));
				default:
					return 2;
			}			
		},

		generateGraphs() {
			console.log("now i'm generating graphs");
			this.chartData = {
				labels: this.deviceData.labels,
				datasets: [
					{
						label: 'Activity',
						data: this.deviceData.steps,
						backgroundColor: this.deviceData.activityColors,
						barThickness: 100
					},
					{
						label: 'Heart rate',
						data: this.deviceData.heartRates,
						backgroundColor: '#ffa500',
						borderColor: '#ffa500',
						type: 'line',
						pointStyle: 'rect',
						pointRadius: 0,
						fill: false
					}
				]};
		},
		displayDeviceTitle(device) {
			return device.NAME + ' <em>' + device.IDENTIFIER + '</em>' 
		},
		filePickDatabase(e) {
			const picker = getFilePickerBuilder("Test")
				.setMultiSelect(false)
				.setModal(true)
				.build();


			picker.pick()
				.then(file => {
					axios.post(OC.linkToOCS('apps/gadgetbridge/api/v1', 2) + 'database', {
						path: file
					}).then((result) => {
                        console.log(result.data.ocs.data.fileId);
                        this.databaseFileId = result.data.ocs.data.fileId;
                        this.databaseFilePath = file.substring(1) // Remove leading slash
                        this.fetchDatabaseData();
                    }).catch(error => { 
					    OC.Notification. showTemporary(t('gadgetbridge', 'The selected file is not a readable Gadgetbridge database'));
				    });
                });
				
        },
        async fetchDatabaseData() {
            console.log("Off I go");
            const response = await axios.get( OC.linkToOCS('apps/gadgetbridge/api/v1', 2) + this.databaseFileId + '/devices');
            let results = response.data.ocs.data;
            console.dir(response.data.ocs.data);

			this.devices = results;
			if (this.devices.length == 1) {
				this.selectedDevice = this.devices[0];
			}
        },
        async loadDeviceData(date) {
			console.log("Imagine I'm loading devices now");
			const response = await axios.get(OC.linkToOCS('apps/gadgetbridge/api/v1', 2) + this.databaseFileId + '/devices/' + this.selectedDevice._id + '/samples/' + date);
			let results = response.data.ocs.data;
			console.dir(results);
			//TODO this might make more sense to do on php side in the future.
			results.forEach((tick) => {
				this.deviceData.labels.push(moment(tick.TIMESTAMP * 1000).calendar());
				let kind = parseInt(tick.RAW_KIND, 10);
				this.deviceData.kinds.push(kind * 10);
				this.deviceData.activityColors.push(this.getActivityColor(kind));
				this.deviceData.steps.push(this.getSteps(kind, tick.STEPS));

				if (tick.HEART_RATE > 0 && tick.HEART_RATE < 255) {
					this.deviceData.lastHeartRate = tick.HEART_RATE;
					this.deviceData.heartRates.push(tick.HEART_RATE);
				} else if (tick.HEART_RATE > 0) {
					this.deviceData.heartRates.push(this.deviceData.lastHeartRate);
					this.deviceData.lastHeartRate = null;
				} else {
					this.deviceData.heartRates.push(null);
				}
			});

			this.generateGraphs()
		},
		
	},
	computed: {
		myStyles() {
			return  {
				height: '700px'
			}
		}
	}
}
</script>
