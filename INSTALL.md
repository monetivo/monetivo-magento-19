###Installing Manually

To install the plugin manually, simply copy folders and refresh the list of plugins:

Copy the folders from the plugin repository to your Magento root folder on the server.
In order to update the list of available plugins, clean the cache:
Go to the Magento administration page [http://your-magento-url/admin].
Go to System > Cache Management.
Select all cache types and click the Flush Magento Cache button.
Note: If the list of plugins doesn't refresh, flush other cache as well.
3. If you have enabled compilation System > Tools > Compilation you have to click Run Compilation Process.

### Installing with modman
To install the plugin with modman just use:
	modman clone repository-link