<template>
	<Content id="gadgetbridgecontent" :class="{'icon-loading': loading}" class="main-display" app-name="gadgetbridge">
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
						<AppNavigationItem 
							:key="device._id" 
							:title="device.NAME"
							v-tooltip="`ID: ${device.IDENTIFIER}`" 
							@click="selectedDevice = device" 
							:class="{active: (selectedDevice == device) }" />
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
					<div class="row">
						<div class="column"><h3>Begin</h3> <datetime type="datetime" :min-datetime="beginRangeTime" use12-hour v-model="startTime" /></div>
						<div class="column"><h3>End</h3> <datetime type="datetime" :min-datetime="endRangeTime" use12-hour v-model="endTime" /></div>
					</div>
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

import { getFilePickerBuilder } from '@nextcloud/dialogs'
import '@nextcloud/dialogs/styles/toast.scss'

import axios from 'axios'
axios.defaults.headers.post['Accept'] = 'application/json';
import BarChart from './BarChart.js'

// Datetime picker
import {Datetime } from 'vue-datetime';
import 'vue-datetime/dist/vue-datetime.css'

export default {
	name: 'Gadgetbridge',
	components: {
		Content,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNew,
		BarChart,
		Datetime
	},
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
			beginRangeTime: {},
			startTime: "",
			endTime: "",
			displayCharts: {
				heartRate: true,
				activity: true,
			},
			chartData: {},
			chartOptions: {
					legend: {
						display: true,
					},
					scales: {
						xAxes: [{
							// type: 'time',
							// unit: 'millisecond',
							// distribution: 'series',
							// time: {
							// 	displayFormats: {
							// 		month: 'MMMM Do YYYY hh:mm a'
							// 	},
							// },
							gridLines: {
								offsetGridLines: true
							},
							stacked: true,
							ticks: {
								autoSkip: true,
								maxTicksLimit: 20
							}
						}],
						yAxes: [{
							stacked: true
						}]
					},
					elements: {
						line: {
							tension: 0
						}
					},
					maintainAspectRatio: false
				}
		}
	},
	watch: {
		databaseFileId: function() {
			this.fetchDatabaseData();
		},
		selectedDevice: function() {
			this.loadDeviceData();
		},
		startTime: function() {
			this.loadDeviceData();
		},
		endTime: function() {
			this.loadDeviceData();
		}
	},
	beforeMount() {
		this.databaseFilePath = $('#gadgetbridgecontent').attr('data-dbpath');
		this.databaseFileId = $('#gadgetbridgecontent').attr('data-dbfileid');
		this.endTime = moment().toISOString();
		this.startTime = moment().subtract(1,'w').toISOString();
	},
	mounted() {

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
						fill: false,
						spanGaps: true
					},
					{
						label: 'Steps',
						data: this.deviceData.steps,
						pointStyle: 'rect',
						pointRadius: 1,
						fill: false,
						spanGaps: true
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
                        this.databaseFileId = result.data.ocs.data.fileId;
                        this.databaseFilePath = file.substring(1) // Remove leading slash
                    }).catch(error => { 
					    OC.Notification. showTemporary(t('gadgetbridge', 'The selected file is not a readable Gadgetbridge database'));
				    });
                });
				
        },
        async fetchDatabaseData() {
            const response = await axios.get( OC.linkToOCS('apps/gadgetbridge/api/v1', 2) + this.databaseFileId + '/devices');
            let results = response.data.ocs.data;

			this.devices = results;
			if (this.devices.length == 1) {
				this.selectedDevice = this.devices[0];
			}
        },
        async loadDeviceData() {
			const response = await axios.get(
					OC.linkToOCS('apps/gadgetbridge/api/v1', 2) + this.databaseFileId + '/devices/' + this.selectedDevice._id + '/samples/' + moment(this.startTime).unix() + '/' + moment(this.endTime).unix());
			let results = response.data.ocs.data;
			//TODO this might make more sense to do on php side in the future.
			results.forEach((tick) => {
				this.deviceData.labels.push(moment(tick.TIMESTAMP * 1000).calendar());
				let kind = parseInt(tick.RAW_KIND, 10);
				this.deviceData.kinds.push(kind * 10);
				this.deviceData.activityColors.push(this.getActivityColor(kind));
				this.deviceData.steps.push({ x: tick.TIMESTAMP * 1000, y: this.getSteps(kind, tick.STEPS)});

				if (tick.HEART_RATE > 0 && tick.HEART_RATE < 255) {
					this.deviceData.lastHeartRate = tick.HEART_RATE;
					this.deviceData.heartRates.push({x: tick.TIMESTAMP * 1000, y: tick.HEART_RATE});
				} else if (tick.HEART_RATE > 0) {
					this.deviceData.heartRates.push({x: tick.TIMESTAMP * 1000, y: this.deviceData.lastHeartRate});
					this.deviceData.lastHeartRate = null;
				} else {
					this.deviceData.heartRates.push(null);
				}
			});
			this.beginRangeTime = moment(this.selectedDevice.STARTTIMESTAMP.TIMESTAMP * 1000).toISOString();
			this.generateGraphs();
		},
		
	},
	computed: {
		myStyles() {
			return  {
				// height: `${this.height}px`,
				// width: '95%',
				// position: 'relative'
			}
		},
		endRangeTime() {
			return moment(this.startTime).add(1,'h').toISOString();
		}
	}
}
</script>

<style scoped>
.main-display {
	width: 95%;
	height: 100%;
	position: relative;
}
.row {
	display: flex;
	justify-content: space-between;
}
.column {
	display: flex;
	flex-direction: column
}
</style>