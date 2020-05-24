<template>
	<Content id="gadgetbridgecontent"
		:class="{'icon-loading': loading}"
		class="main-display"
		app-name="gadgetbridge">
		<AppNavigation>
			<AppNavigationNew v-if="!loading"
				:text="t('gadgetbridge', 'Select Database')"
				:disabled="false"
				button-id="new-gadgetbridge-button"
				button-class="icon-add"
				@click="filePickDatabase" />
			<AppNavigationItem v-if="databaseFilePath" :title="databaseFilePath" icon="icon-folder">
				<AppNavigationItem title="Test" style="display:none" /> <!-- This is stupid, but makes the nesting work.  FIXME -->
				<template v-for="device in devices">
					<AppNavigationItem
						:key="device._id"
						v-tooltip="`ID: ${device.IDENTIFIER}`"
						:title="device.NAME"
						:class="{active: (selectedDevice == device) }"
						@click="selectedDevice = device" />
				</template>
			</AppNavigationItem>
		</AppNavigation>
		<AppContent>
			<div v-if="!selectedDevice" id="empty-content">
				<div class="icon-activity" />
				<h2>No Data found</h2>
				<p>Please Import data from android app to continue</p>
			</div>
			<template v-else>
				<BarChart class="main-wrapper" :chart-data="chartData" :options="chartOptions" />
				<div class="row">
					<div class="column">
						<DateTime
							v-model="startTime"
							type="datetime"
							:min-datetime="beginRangeTime"
							use12-hour />
					</div>
					<div class="column">
						<DateTime
							v-model="endTime"
							type="datetime"
							:min-datetime="endRangeTime"
							use12-hour />
					</div>
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

import { showError, getFilePickerBuilder } from '@nextcloud/dialogs'
import '@nextcloud/dialogs/styles/toast.scss'
import { generateOcsUrl } from '@nextcloud/router'
import axios from 'axios'
import BarChart from './BarChart.js'

// Datetime picker
import { Datetime } from 'vue-datetime'
import 'vue-datetime/dist/vue-datetime.css'
import moment from 'moment'

axios.defaults.headers.post['Accept'] = 'application/json'

export default {
	name: 'Gadgetbridge',
	components: {
		Content,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNew,
		BarChart,
		DateTime: Datetime,
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
				lastHeartRate: null,
				stepsPerDay: {},
			},
			beginRangeTime: {
				default: moment().subtract(1, 'w').toISOString(),
			},
			startTime: '',
			endTime: '',
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
						gridLines: {
							offsetGridLines: true,
						},
						stacked: true,
						ticks: {
							autoSkip: true,
							maxTicksLimit: 20,
						},
					}],
					yAxes: [{
						stacked: true,
					}],
				},
				elements: {
					line: {
						tension: 0,
					},
				},
				maintainAspectRatio: false,
			},
		}
	},
	computed: {
		endRangeTime() {
			return moment(this.startTime).add(1, 'h').toISOString()
		},
	},
	watch: {
		databaseFileId: function() {
			this.fetchDatabaseData()
		},
		selectedDevice: function() {
			this.loadDeviceData()
		},
		startTime: function() {
			this.loadDeviceData()
		},
		endTime: function() {
			this.loadDeviceData()
		},
	},
	beforeMount() {
		this.databaseFilePath = document.querySelector('#gadgetbridgecontent').getAttribute('data-dbpath')
		this.databaseFileId = document.querySelector('#gadgetbridgecontent').getAttribute('data-dbfileid')
		this.endTime = moment().toISOString()
		this.startTime = moment().subtract(1, 'w').toISOString()
	},
	methods: {
		generateGraphs() {
			this.chartData = {
				labels: this.deviceData.labels,
				datasets: [
					{
						label: 'Activity',
						data: this.deviceData.steps,
						backgroundColor: this.deviceData.activityColors,
						barThickness: 100,
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
						spanGaps: true,
					},
				] }
		},
		async filePickDatabase(e) {
			const picker = getFilePickerBuilder()
				.setMultiSelect(false)
				.setModal(true)
				.build()
			const file = await picker.pick()
			if (file) {
				try {
					const result = await axios.post(generateOcsUrl('apps/gadgetbridge/api/v1', 2) + 'database', {
						path: file,
					})
					if (!result) return
					this.databaseFileId = result.data.ocs.data.fileId
					this.databaseFilePath = file.substring(1) // Remove leading slash
				} catch (err) {
					showError(t('gadgetbridge', 'The selected file is not a readable Gadgetbridge database'))
				}
			}
		},
		async fetchDatabaseData() {
			const response = await axios.get(generateOcsUrl('apps/gadgetbridge/api/v1', 2) + this.databaseFileId + '/devices')
			const results = response.data.ocs.data

			this.devices = results
			if (this.devices.length === 1) {
				this.selectedDevice = this.devices[0].data
			}
		},
		async loadDeviceData() {
			if (this.selectedDevice === null) return
			const response = await axios.get(
				generateOcsUrl('apps/gadgetbridge/api/v1', 2) + this.databaseFileId + '/devices/' + this.selectedDevice.deviceId + '/samples/' + moment(this.startTime).unix() + '/' + moment(this.endTime).unix())
			const results = response.data.ocs.data
			this.deviceData.labels = results.TIMESTAMPS.map((item) => {
				return moment(item).calendar()
			})
			this.deviceData.kinds = results.KINDS
			this.deviceData.activityColors = results.ACTIVITY_COLORS
			this.deviceData.heartRates = results.HEART_RATES
			this.deviceData.steps = results.STEPS
			this.beginRangeTime = moment(this.selectedDevice.beginningDateTimestamp.TIMESTAMP * 1000).toISOString()
			this.generateGraphs()
		},

	},
}
</script>

<style>
.main-display {
	width: 95%;
	height: 100%;
	position: relative;
}

.main-wrapper {
	margin-top: 30px;
}

.row {
	display: flex;
	justify-content: space-between;
}

.column {
	display: flex;
	flex-direction: column
}

.vdatetime-input {
	width: auto;
}
</style>
