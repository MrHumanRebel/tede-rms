# 🎭 TeDeRMS – Bérléskezelő Rendszer

A **TeDeRMS** egy fejlett bérléskezelő rendszer színházi, audiovizuális (AV) és broadcast célokra. A rendszer **PHP** nyelven készült a **Twig** sablonmotor segítségével, és egy előre elkészített **Docker-konténerben** kerül telepítésre. 🚀

A rendszer elérhető **hosztolt megoldásként** vagy **saját üzemeltetésre** Docker-konténerként. 🏗️

## ✨ Főbb jellemzők

- ✅ Kifejezetten a **TéDé Rendezvények** számára személyre szabott verzió
- ✅ Modern és felhasználóbarát felület 🎨
- ✅ Integrált eszközbérlés-kezelés 🎤🎬
- ✅ Teljes körű adminisztrációs lehetőségek 🔧
- ✅ Könnyű telepítés és skálázhatóság **Docker** segítségével 🐳

## 🛠️ Telepítés

A rendszer futtatásához szükséges:

### 1️⃣ **Docker és Docker Compose telepítése**
Győződj meg róla, hogy **Docker** és **Docker Compose** telepítve van a rendszeren.

### 2️⃣ **A repó klónozása és a megfelelő konfiguráció beállítása**
```sh
git clone https://github.com/TEDERMS/berleskezelo.git
cd berleskezelo
cp .env.example .env
nano .env  # Itt állítsd be a szükséges konfigurációkat
```

### 3️⃣ **A konténer indítása**
```sh
docker-compose up -d
```

## 📞 Támogatás

Ha kérdésed van vagy támogatásra van szükséged, lépj kapcsolatba a **TéDé Rendezvények** csapatával. 🤝