FROM modpreneur/trinity-test:0.2

MAINTAINER Martin Kolek <kole@modpreneur.com>

#ADD . /var/app

WORKDIR /var/app

#RUN chmod +x entrypoint.sh

ENTRYPOINT ["fish", "entrypoint.sh"]