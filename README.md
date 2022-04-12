<img src="https://dealt.fr/logo.svg" width="200"/>

### Dealt Prestashop Module

> Supported : prestashop@1.7.8

---

### Dev environment 🏗

Start the docker containers to launch the mysql service & the prestashop instances. The dealt module is automatically mounted in the containers.

```bash
docker-compose up
```

The PS admin panel will be located at `localhost:8080/admin-dealt`

Admin credentials :

```bash
email: demo@prestashop.com
password: prestashop_demo
```

> You can override these settings in the .env file at the root of this project (full list of available PS environment variables available [🔗 here](https://hub.docker.com/r/prestashop/prestashop/))

#### VSCode Setup

Use the PHP Intelephense extension for VSCode for code completion and PS class comprehension :

```bash
### Clone the PrestaShop repository on your device
git clone https://github.com/PrestaShop/PrestaShop.git
```

Edit your `.vscode/settings.json` :

```json
{
  "intelephense.environment.includePaths": [
    "/Path/where/you/cloned/the/PrestaShop/repo"
  ]
}
```

> Make sure to build the PrestaShop cache for class stubbing comprehension in VSCode (`PrestaShop/var/cache/dev/class_stub.php` needs to be compiled) - You may need to restart VSCode or manually trigger a workspace indexing.

---
