<!--
  - SPDX-FileCopyrightText: 2026 Arawa
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcSettingsSection class="matomo-settings" :name="t('matomo', 'Matomo Tracking')">
		<p class="settings-hint">
			If you have no Matomo instance, go to <a href="https://matomo.org" target="_blank">matomo.org</a> for further instructions.
		</p>
		<NcTextField v-model="matomo.siteId"
			class="matomo-input"
			:label="t('matomo', 'Site ID')"
			:placeholder="t('matomo', 'get this value from your Matomo instance')"
			:class="{
				'matomo-site-id': true,
				'matomo-success': statuses.siteId === 'success',
				'matomo-error': statuses.siteId === 'error',
			}"
			maxlength="10"
			@update:model-value="updateSetting('siteId', matomo.siteId)" />
		<NcTextField v-model="matomo.url"
			class="matomo-input"
			:label="t('matomo', 'Matomo URL')"
			:placeholder="placeholder"
			:class="{
				'matomo-success': statuses.url === 'success',
				'matomo-error': statuses.url === 'error',
			}"
			maxlength="250"
			type="url"
			@update:model-value="updateSetting('url', matomo.url)" />
		<NcCheckboxRadioSwitch v-model="matomo.trackDir"
			class="matomo-input"
			type="switch"
			:class="{
				'matomo-success': statuses.trackDir === 'success',
				'matomo-error': statuses.trackDir === 'error',
			}"
			@update:model-value="updateSetting('trackDir', matomo.trackDir)">
			{{ t('matomo', 'Track file browsing') }}
		</NcCheckboxRadioSwitch>
		<NcCheckboxRadioSwitch v-model="matomo.trackUser"
			class="matomo-input"
			type="switch"
			:class="{
				'matomo-success': statuses.trackUser === 'success',
				'matomo-error': statuses.trackUser === 'error',
			}"
			@update:model-value="updateSetting('trackUser', matomo.trackUser)">
			{{ t('matomo', 'Track user id') }}
		</NcCheckboxRadioSwitch>
	</NcSettingsSection>
</template>

<script setup lang="ts">
import { computed, onBeforeMount, ref, reactive } from 'vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'

const config = loadState('matomo', 'config')

const matomo = reactive({
	url: config.matomoUrl,
	siteId: config.matomoSiteId,
	trackDir: config.matomoTrackDir,
	trackUser: config.matomoTrackUser,
})

const statuses = ref({})

const placeholder = computed(() => `e.g. //${window.location.host}/matomo/`)

/**
 * Updates a Matomo setting by PUT request
 * @param {string} key - the setting key to update
 * @param {string|boolean} value - the new value for the setting
 */
async function updateSetting(key, value) {
	try {
		const response = await axios.put(
			generateUrl(`/apps/matomo/settings/${key}`),
			{
				value,
			},
		)

		statuses.value[key] = response.data.status

		setTimeout(() => {
			delete statuses.value[key]
		}, 1000)
	} catch (e) {
		statuses.value[key] = 'error'

		setTimeout(() => {
			delete statuses.value[key]
		}, 1000)
	}
}

onBeforeMount(() => {
	// If we arrive here, the "/matomo/settings" is not blocked, we can hide the adblocker warning
	const adblockerWarning = document.querySelector('#matomoAdblockerWarning')

	if (adblockerWarning) {
		adblockerWarning.style.display = 'none'
	}
})

</script>
<style lang="scss" scoped>
.matomo-settings {
	max-width: 600px;

	.matomo-site-id {
		max-width: 120px;
	}
}

.matomo-input {
	display: flex;

	&.input-field {
		margin-top: 20px;
	}

	&::after {
		font-size: 18px;
		font-weight: bold;
		margin-left: 8px;
		content: " ";
	}

	&.matomo-success::after {
		content: "✓";
		color: #4eb387;
		opacity: 1;
		transition: opacity 800ms ease, transform 800ms ease;
	}

	&.matomo-error::after {
		content: "✗";
		color: #f65c38;
		opacity: 1;
		transition: opacity 800ms ease, transform 800ms ease;
	}
}

.settings-hint {
	font-style: italic;
}

</style>
