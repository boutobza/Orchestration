#docker build -t mydebian .
FROM debian:latest
RUN apt-get update && apt-get install -y openssh-server
RUN mkdir /var/run/sshd
RUN useradd -ms /bin/bash user
RUN echo 'user:user' | chpasswd

EXPOSE 22
CMD ["/usr/sbin/sshd", "-D"]
