// /**
//  * @copyright (c) 2020 Dan Meltzer <dmeltzer.devel@gmail.com>
//  *
//  * @author Dan Meltzer <dmeltzer.devel@gmail.com>
//  *
//  * This file is licensed under the Affero General Public License version 3 or
//  * later. See the COPYING file.
//  */
import { Bar, mixins } from 'vue-chartjs'
const { reactiveProp } = mixins
export default {
	extends: Bar,
	mixins: [reactiveProp],
	props: {
		options: {
			type: Object,
			default: null,
		},
	},
	mounted() {
		this.renderChart(this.chartData, this.options)
	},
}
