FROM dockette/php:5.6

LABEL maintainer="thejoeejoee@gmail.com"

COPY . /srv/

WORKDIR /srv/

CMD ["php", "-S", "0.0.0.0:8000", "-t", "/srv/www"]