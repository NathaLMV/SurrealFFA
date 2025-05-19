# SurrealFFA

SurrealFFA is a professional plugin for PocketMine-MP that offers a competitive and fun Free-For-All (FFA) mode, fully integrated with the API [LibSurrealDB](https://github.com/NathaLMV/LibSurrealDB) para el manejo avanzado de estadísticas y almacenamiento persistente.

---

## Characteristics
- Fast and fluid FFA system.
- Statistics registration in SurrealDB (kills, deaths).
- Full integration with the LibSurrealDB API for asynchronous queries.
- Standings and real-time statistics.
- Modular design and easy to extend.

---

## Requirements

- PocketMine-MP version 5.0 or higher.
- [LibSurrealDB](https://github.com/NathaLMV/LibSurrealDB) installed and working properly.
- Accessible SurrealDB server.

---

## Installation

1. Download the SurrealFFA plugin and the LibSurrealDB api.
2. Place both `.phar` files in the `plugins` folder on your PocketMine-MP server.
3. Make sure SurrealDB is running and configured.
4. Configure `config.yml` to connect to your SurrealDB database.
5. Restart your PocketMine-MP server.

---

## Configuración

Example `config.yml`:

```yaml
ffa-world: world
```
