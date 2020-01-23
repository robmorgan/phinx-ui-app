![Phinx UI App](https://github.com/robmorgan/phinx-ui-app/blob/master/_docs/phinx-ui-app.png)

# Phinx UI App

A Simple Web UI that shows the Phinx migration status built using the Slim Framework (v4).

## Configuration

The app is configured with the following environment variables:

- `MYSQL_HOST`: The MySQL host name.
- `MYSQL_DATABASE`: The MySQL database name.
- `MYSQL_USERNAME`: The username of the MySQL user.
- `MYSQL_PASSWORD`: The password of the MySQL user.
- `INSTANCE_CONNECTION_NAME`: This is used when deploying to Google Cloud Run and enables PDO Unix Socket support.

You can refer to the configuration in the `app/settings.php` file. The root of the repository contains a `phinx.php` file which has been configured to use these values.

**Note:** Saving credentials in environment variables is convenient, but not secure - consider a more secure solution such as [Cloud KMS](https://cloud.google.com/kms/) to help keep secrets safe.

## Getting Started

If you have Docker and Docker Compose installed, then you can simply run:

```bash
$ docker-compose up
```

To download and build all of the necessary dependencies.

### Creating Database Migrations

To create a new migration run:

```bash
$ docker-compose run php php vendor/bin/phinx create CreateUsersTable
```

### Executing Database Migrations

To execute database migrations run:

```bash
$ docker-compose run php php vendor/bin/phinx migrate -e development
```

## Deployment

This app has been designed to be deployed to Google Cloud Run and is configured to automatically execute any outstanding migrations on startup. You can see the `public/index.php` for the code logic that enables this.
The root of the repo contains a `cloudbuild.yaml` file that will automatically package the app into a Docker
container when it is pushed to Google Cloud Source Repositories.

If you would like to deploy the necessary resources to run this app on Google Cloud Run, then check out my
[terraform-cloudrun-example](https://github.com/robmorgan/terraform-cloudrun-example) repo.
