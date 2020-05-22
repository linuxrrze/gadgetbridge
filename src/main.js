// /**
//  * @copyright (c) 2017 Joas Schilling <coding@schilljs.com>
//  *
//  * @author Joas Schilling <coding@schilljs.com>
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

new Vue({ // eslint-disable-line no-new
	el: '#gadgetbridgecontent',
	render: h => h(Gadgetbridge),
})
