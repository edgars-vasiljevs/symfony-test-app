### Requirements

- composer (https://getcomposer.org/)
- Docker & Docker-Compose (https://docs.docker.com/get-docker/)

### Project setup

Clone project repo:
```
git clone https://github.com/edgars-vasiljevs/symfony-test-app.git weather
cd weather
```

Run app using docker-compose
```
cd docker
docker-compose up --build -d
```

### App Usage
- Fetching forecast with cache: http://127.0.0.1:8090/weather
- Fetching latest forecast (no cache): http://127.0.0.1:8090/weather/refresh
