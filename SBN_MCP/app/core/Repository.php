<?php
/**
 * Clase Base Repository — Patrón de Acceso a Datos.
 *
 * Centraliza consultas CRUD genéricas, paginación y helpers de query
 * para todas las entidades del sistema. Cada modelo extiende esta clase
 * y define su tabla, columnas y reglas de validación.
 *
 * @package App\Core
 */

namespace App\Core;

use PDO;

abstract class Repository
{
    /** Nombre de la tabla en la base de datos. */
    protected string $table = '';

    /** Clave primaria de la tabla. */
    protected string $primaryKey = 'id';

    /** Columnas permitidas para SELECT (evita SELECT *). */
    protected array $columns = ['*'];

    /** Columnas que pueden usarse en ORDER BY (whitelist). */
    protected array $sortableColumns = [];

    /**
     * Obtiene los nombres de columna para SELECT.
     */
    protected function cols(): string
    {
        return $this->columns === ['*'] ? '*' : implode(', ', $this->columns);
    }

    /**
     * Busca un registro por su clave primaria.
     */
    public function find(int $id): ?array
    {
        return Database::fetch(
            "SELECT {$this->cols()} FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1",
            ['id' => $id]
        );
    }

    /**
     * Busca un registro con una condición WHERE personalizada.
     */
    public function findBy(array $conditions): ?array
    {
        if (empty($conditions)) {
            return null;
        }

        $where = [];
        $params = [];
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        return Database::fetch(
            "SELECT {$this->cols()} FROM {$this->table} WHERE " . implode(' AND ', $where) . ' LIMIT 1',
            $params
        );
    }

    /**
     * Obtiene todos los registros con filtros opcionales.
     */
    public function findAll(array $conditions = [], string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT {$this->cols()} FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $placeholders = [];
                    foreach ($value as $i => $v) {
                        $key = "{$column}_{$i}";
                        $placeholders[] = ":{$key}";
                        $params[$key] = $v;
                    }
                    $where[] = "{$column} IN (" . implode(', ', $placeholders) . ')';
                } else {
                    $where[] = "{$column} = :{$column}";
                    $params[$column] = $value;
                }
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if ($orderBy !== '' && $this->isValidOrderBy($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit > 0) {
            $sql .= ' LIMIT :limit';
            if ($offset > 0) {
                $sql .= ' OFFSET :offset';
            }
        }

        $stmt = Database::getInstance()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        if ($limit > 0) {
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            if ($offset > 0) {
                $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Paginación con filtros y ordenamiento.
     */
    public function paginate(int $page = 1, int $perPage = 20, array $conditions = [], string $orderBy = ''): array
    {
        $page = max(1, $page);

        $countSql = "SELECT COUNT(*) AS n FROM {$this->table}";
        $countParams = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $placeholders = [];
                    foreach ($value as $i => $v) {
                        $key = "{$column}_{$i}";
                        $placeholders[] = ":{$key}";
                        $countParams[$key] = $v;
                    }
                    $where[] = "{$column} IN (" . implode(', ', $placeholders) . ')';
                } else {
                    $where[] = "{$column} = :{$column}";
                    $countParams[$column] = $value;
                }
            }
            $countSql .= ' WHERE ' . implode(' AND ', $where);
        }

        $total = (int) (Database::fetch($countSql, $countParams)['n'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $items = $this->findAll($conditions, $orderBy, $perPage, $offset);

        return [
            'items'      => $items,
            'page'       => $page,
            'perPage'    => $perPage,
            'total'      => $total,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * Inserta un registro y retorna el ID generado.
     * Las columnas se validan contra una whitelist.
     */
    public function create(array $data): int
    {
        $data = $this->filterAllowedColumns($data);
        if (empty($data)) {
            throw new \InvalidArgumentException('No hay columnas válidas para insertar');
        }

        $columns      = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        Database::query($sql, $data);
        return Database::lastInsertId();
    }

    /**
     * Actualiza un registro por su clave primaria.
     * Las columnas se validan contra una whitelist.
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterAllowedColumns($data);
        if (empty($data)) {
            throw new \InvalidArgumentException('No hay columnas válidas para actualizar');
        }

        $columns = array_keys($data);
        $set     = array_map(fn($col) => "{$col} = :{$col}", $columns);

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :pk',
            $this->table,
            implode(', ', $set),
            $this->primaryKey
        );

        $data['pk'] = $id;
        $stmt = Database::getInstance()->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Elimina un registro.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = Database::getInstance()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Cuenta registros con condiciones opcionales.
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) AS n FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return (int) (Database::fetch($sql, $params)['n'] ?? 0);
    }

    /**
     * Ejecuta una consulta con suma agregada.
     */
    public function sum(string $column, array $conditions = []): float
    {
        $sql = "SELECT COALESCE(SUM({$column}), 0) AS total FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $col => $value) {
                $where[] = "{$col} = :{$col}";
                $params[$col] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return (float) (Database::fetch($sql, $params)['total'] ?? 0);
    }

    /**
     * Verifica si un ORDER BY es válido (whitelist).
     */
    protected function isValidOrderBy(string $orderBy): bool
    {
        if (empty($this->sortableColumns)) {
            return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\s+(ASC|DESC))?$/i', $orderBy) === 1;
        }

        $parts = preg_split('/\s+/', trim($orderBy));
        return in_array($parts[0], $this->sortableColumns, true);
    }

    /**
     * Ejecuta una consulta raw con parámetros bound.
     */
    protected function rawQuery(string $sql, array $params = []): array
    {
        return Database::fetchAll($sql, $params);
    }

    /**
     * Ejecuta una consulta raw que retorna un solo registro.
     */
    protected function rawFetch(string $sql, array $params = []): ?array
    {
        return Database::fetch($sql, $params);
    }

    /**
     * Filtra el array de datos para que solo contenga columnas permitidas.
     * Previene SQL injection en nombres de columna en INSERT/UPDATE dinámicos.
     */
    protected function filterAllowedColumns(array $data): array
    {
        // Si no hay whitelist definida, validar que los nombres sean seguros
        if (empty($this->columns) || $this->columns === ['*']) {
            return array_filter(
                $data,
                fn($key) => preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key),
                ARRAY_FILTER_USE_KEY
            );
        }

        return array_intersect_key($data, array_flip($this->columns));
    }
}
