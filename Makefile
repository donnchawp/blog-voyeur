.DEFAULT_GOAL := help

## Release
release: ## Prepare a release PR. Usage: make release VERSION=x.y.z
	@test -n "$(VERSION)" || { echo "Usage: make release VERSION=x.y.z"; exit 1; }
	node scripts/prepare-release.mjs $(VERSION)

build: ## Build build/blog-voyeur.zip from tracked files at HEAD
	./scripts/build-plugin.sh

i18n: ## Regenerate translations (no-op: this plugin ships no translations)
	@echo "No POT generation step for Blog Voyeur."

clean: ## Remove the build/ directory
	rm -rf build

## Help
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-12s\033[0m %s\n", $$1, $$2}'

.PHONY: release build i18n clean help
