# add the following column after hosting_url
# $table->boolean('hosting_redirect_subdomain')->default(true);

ALTER TABLE `blogs` ADD `hosting_redirect_subdomain` BOOLEAN NOT NULL DEFAULT TRUE AFTER `hosting_url`;