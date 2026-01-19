FROM node:18-alpine

WORKDIR /app

# Installer dépendances
COPY package*.json ./
RUN npm install

# Copier le projet
COPY . .

# Build Next.js
RUN npm run build

EXPOSE 3000

CMD ["npm", "run", "start"]
