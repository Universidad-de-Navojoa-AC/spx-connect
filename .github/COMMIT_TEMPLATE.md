# Plantilla de Commit – Conventional Commits

# Formato básico:
<type>[optional scope]: <short description>

[Optional detailed description in one or more paragraphs]

[Optional footer(s), e.g., BREAKING CHANGE: description of incompatibility]

---

# Tipos de commit recomendados:

- feat:     Nueva funcionalidad
- fix:      Corrección de bug
- perf:     Mejora de rendimiento
- docs:     Cambios en documentación
- chore:    Tareas de mantenimiento (scripts, dependencias, etc.)
- refactor: Refactor sin cambios funcionales
- test:     Agregar o modificar tests

---

# Ejemplos de commits válidos:

- feat(auth): agregar login con OAuth
- fix(api): corregir validación de email
- perf(db): optimizar consulta de usuarios
- docs: actualizar README con instrucciones de instalación
- chore: actualizar dependencias de npm
- refactor!: renombrar método público getUserData a fetchUserData
- feat: eliminar endpoint /v1/login
- BREAKING CHANGE: usar /v2/login en su lugar

---

# Notas:

- `[optional scope]` puede ser usado para indicar módulo, componente o área afectada, por ejemplo `(auth)`, `(api)`, `(db)`.  
- `BREAKING CHANGE:` en el footer indica que el commit rompe compatibilidad → genera **major release**.  
- Semantic Release detecta automáticamente el tipo de release según el tipo de commit.

