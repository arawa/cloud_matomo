# SPDX-FileCopyrightText: 2026 Arawa
# SPDX-License-Identifier: AGPL-3.0-or-later

app_name=$(notdir $(CURDIR))
build_directory=$(CURDIR)/build
source_build_directory=$(build_directory)/artifacts/source
source_package_name=$(source_build_directory)/$(app_name)
appstore_build_directory=$(build_directory)/artifacts/appstore
appstore_sign_directory=$(build_directory)/sign
appstore_package_name=$(appstore_build_directory)/$(app_name)
cert_directory=$(HOME)/.nextcloud/certificates
# Command running occ, e.g. make appstore occ="sudo -u www-data php ../../occ"
occ=php ../../occ
# Files and directories shipped in the app store package
appstore_files=appinfo css img js l10n lib templates LICENSE
npm=$(shell which npm 2> /dev/null)
composer=$(shell which composer 2> /dev/null)

all: build

# Installs PHP and JS dependencies and builds the frontend
.PHONY: build
build: composer npm

# Installs PHP dependencies, downloading composer if it is not available
.PHONY: composer
composer:
ifeq (, $(composer))
	@echo "No composer command available, downloading a copy from the web"
	mkdir -p $(build_directory)/tools
	curl -sS https://getcomposer.org/installer | php -- --install-dir=$(build_directory)/tools
	php $(build_directory)/tools/composer.phar install --prefer-dist
else
	composer install --prefer-dist
endif

# Installs JS dependencies and builds the frontend for production
.PHONY: npm
npm:
	$(npm) ci
	$(npm) run build

.PHONY: clean
clean:
	rm -rf $(build_directory)

.PHONY: distclean
distclean: clean
	rm -rf vendor node_modules js css

.PHONY: dist
dist: source appstore

# Builds a tarball of the sources
.PHONY: source
source:
	rm -rf $(source_build_directory)
	mkdir -p $(source_build_directory)
	tar -czf $(source_package_name).tar.gz \
		--exclude-vcs \
		--exclude="$(app_name)/build" \
		--exclude="$(app_name)/archives" \
		--exclude="$(app_name)/node_modules" \
		--exclude="$(app_name)/vendor" \
		--exclude="$(app_name)/js" \
		--exclude="$(app_name)/css" \
		--exclude="$(app_name)/.env" \
		--exclude="$(app_name)/*.log" \
		--exclude="$(app_name)/.php-cs-fixer.cache" \
		-C $(dir $(CURDIR)) $(app_name)

# Builds the release tarball for the app store. When the app key is present, the app
# files are signed with occ (appinfo/signature.json) and the tarball with openssl (.ncsig).
.PHONY: appstore
appstore: npm
	rm -rf $(appstore_build_directory) $(appstore_sign_directory)
	mkdir -p $(appstore_build_directory) $(appstore_sign_directory)/$(app_name)
	cp -r $(appstore_files) $(appstore_sign_directory)/$(app_name)
	@if [ -f $(cert_directory)/$(app_name).key ]; then \
		if [ ! -f $(cert_directory)/$(app_name).crt ]; then \
			echo "Missing certificate $(cert_directory)/$(app_name).crt" >&2; \
			exit 1; \
		fi; \
		echo "Signing app files with occ"; \
		$(occ) integrity:sign-app \
			--privateKey=$(cert_directory)/$(app_name).key \
			--certificate=$(cert_directory)/$(app_name).crt \
			--path=$(appstore_sign_directory)/$(app_name); \
		if [ ! -f $(appstore_sign_directory)/$(app_name)/appinfo/signature.json ]; then \
			echo "occ did not create appinfo/signature.json" >&2; \
			exit 1; \
		fi; \
	else \
		echo "No key found in $(cert_directory)/$(app_name).key, app files not signed"; \
	fi
	tar -czf $(appstore_package_name).tar.gz -C $(appstore_sign_directory) $(app_name)
	@if [ -f $(cert_directory)/$(app_name).key ]; then \
		echo "Signing package with openssl"; \
		openssl dgst -sha512 -sign $(cert_directory)/$(app_name).key $(appstore_package_name).tar.gz \
			| openssl base64 > $(appstore_package_name).tar.gz.ncsig; \
		echo "Signature written to $(appstore_package_name).tar.gz.ncsig"; \
	else \
		echo "No key found in $(cert_directory)/$(app_name).key, package not signed"; \
	fi
