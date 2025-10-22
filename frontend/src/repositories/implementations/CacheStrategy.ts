export interface ICacheStrategy {
  get<T>(key: string): T | null
  set<T>(key: string, value: T, ttl?: number): void
  delete(key: string): void
  clear(): void
  has(key: string): boolean
  keys(): string[]
}

export class MemoryCacheStrategy implements ICacheStrategy {
  private cache: Map<string, { value: unknown; expires: number }> = new Map()
  private defaultTTL = 5 * 60 * 1000

  get<T>(key: string): T | null {
    const item = this.cache.get(key)
    if (!item) return null

    if (Date.now() > item.expires) {
      this.cache.delete(key)
      return null
    }

    return item.value as T
  }

  set<T>(key: string, value: T, ttl = this.defaultTTL): void {
    const expires = Date.now() + ttl
    this.cache.set(key, { value, expires })
  }

  delete(key: string): void {
    this.cache.delete(key)
  }

  clear(): void {
    this.cache.clear()
  }

  has(key: string): boolean {
    const item = this.cache.get(key)
    if (!item) return false

    if (Date.now() > item.expires) {
      this.cache.delete(key)
      return false
    }

    return true
  }

  keys(): string[] {
    return Array.from(this.cache.keys())
  }
}

export class LocalStorageCacheStrategy implements ICacheStrategy {
  private prefix = 'app_cache_'
  private defaultTTL = 30 * 60 * 1000

  get<T>(key: string): T | null {
    try {
      const item = localStorage.getItem(this.prefix + key)
      if (!item) return null

      const parsed = JSON.parse(item)
      if (Date.now() > parsed.expires) {
        localStorage.removeItem(this.prefix + key)
        return null
      }

      return parsed.value as T
    } catch {
      return null
    }
  }

  set<T>(key: string, value: T, ttl = this.defaultTTL): void {
    try {
      const expires = Date.now() + ttl
      const item = { value, expires }
      localStorage.setItem(this.prefix + key, JSON.stringify(item))
    } catch {
      //
    }
  }

  delete(key: string): void {
    localStorage.removeItem(this.prefix + key)
  }

  clear(): void {
    const keys = Object.keys(localStorage)
    keys.forEach((key) => {
      if (key.startsWith(this.prefix)) {
        localStorage.removeItem(key)
      }
    })
  }

  has(key: string): boolean {
    const item = this.get(key)
    return item !== null
  }

  keys(): string[] {
    const keys = Object.keys(localStorage)
    return keys
      .filter((key) => key.startsWith(this.prefix))
      .map((key) => key.substring(this.prefix.length))
  }
}
