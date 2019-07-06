#https://mherman.org/blog/dockerizing-an-angular-app/
FROM node:10.15.0

# install chrome for protractor tests
RUN wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add -
RUN sh -c 'echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list'
RUN apt-get update && apt-get install -yq google-chrome-stable dos2unix

# set working directory
RUN mkdir /usr/src/app
WORKDIR /usr/src/app

# add `/usr/src/app/node_modules/.bin` to $PATH
ENV PATH /usr/src/app/node_modules/.bin:$PATH

# install and cache app dependencies
COPY package.json /usr/src/app/package.json
RUN npm install
RUN npm install -g @angular/cli

# Copy package.json for later comparison on runtime
RUN cp /usr/src/app/package.json /usr/src/package.json

# add app
COPY . /usr/src/app

COPY ./docker-run.sh /usr/bin/docker-run.sh

RUN dos2unix /usr/bin/docker-run.sh /usr/src/app/package.json

ENTRYPOINT [ "/bin/bash"]
CMD ["/usr/bin/docker-run.sh"]

