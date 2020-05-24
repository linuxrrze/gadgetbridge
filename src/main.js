// /**
//  * @copyright (c) 2017 Joas Schilling <coding@schilljs.com>
//  * @copyright (c) 2020 Dan Meltzer <dmeltzer.devel@gmail.com>
//  *
//  * @author Joas Schilling <coding@schilljs.com>
//  * @author Dan Meltzer <dmeltzer.devel@gmail.com>
//  *
//  * This file is licensed under the Affero General Public License version 3 or
//  * later. See the COPYING file.
//  */

import Vue from 'vue'
import { translate, translatePlural } from 'nextcloud-l10n'
import Gadgetbridge from './Gadgetbridge'

import { Tooltip } from '@nextcloud/vue'
Vue.directive('tooltip', Tooltip)

Vue.prototype.t = translate
Vue.prototype.n = translatePlural
Vue.prototype.OC = window.OC
Vue.prototype.OCA = window.OCA

new Vue({ // eslint-disable-line no-new
	el: '#gadgetbridgecontent',
	render: h => h(Gadgetbridge),
})
