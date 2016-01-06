# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  # All Vagrant configuration is done here. The most common configuration
  # options are documented and commented below. For a complete reference,
  # please see the online documentation at vagrantup.com.

  # Every Vagrant virtual environment requires a box to build off of.
  config.vm.box = "rescueme/precise64"

  # Create a private network, which allows host-only access to the machine
  # using a specific IP and port.
  config.vm.network "private_network", ip: "192.168.33.10", port: 80


  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  config.vm.synced_folder "src", "/var/www/rescueme", owner: "www-data", group: "www-data"

  # Rename default folder
  config.vm.synced_folder '.', '/vagrant', disabled: true
  config.vm.synced_folder '.', '/rescueme'

end
