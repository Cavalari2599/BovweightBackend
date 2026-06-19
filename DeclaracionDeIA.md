## Declaración de Uso de Inteligencia Artificial

En cumplimiento del punto 9.2 del enunciado del proyecto, el equipo declara de
forma explícita el uso de herramientas de inteligencia artificial generativa
durante el desarrollo de BovWeight CR.

### Herramientas utilizadas

| Herramienta | Propósito de uso | Cómo se validó |
|---|---|---|
| Claude (Anthropic) | Apoyo en la generación de código (backend Laravel, frontend Vue/Ionic y microservicio ML) a partir de instrucciones explícitas del equipo. | Todo el código fue revisado, comprendido y adaptado por el equipo a los patrones y convenciones del proyecto; verificado mediante pruebas unitarias y de integración y el pipeline CI/CD. |
| Gemini (Google) | Apoyo complementario en la generación y revisión de código. | Misma validación: revisión manual, adaptación a la arquitectura propia y verificación mediante pruebas automatizadas. |
| atoms.dev | Apoyo en la mejora del estilo visual de las vistas de la aplicación. | El equipo revisó y ajustó manualmente los resultados para mantener coherencia con el diseño y la identidad visual definidos por el equipo. |

### Alcance y límites del uso de IA

El trabajo de ingeniería de software fue realizado íntegramente por el equipo. En
particular, **no** se utilizó IA para:

- La elicitación, análisis y especificación de requerimientos (ERS, historias de
  usuario, casos de uso).
- El diseño de los diagramas (modelo de dominio, C4, BPMN/actividades, secuencia,
  modelo lógico y físico de base de datos).
- Las decisiones de arquitectura y la definición de los patrones de diseño.

La IA se empleó únicamente como apoyo en la **implementación**. El equipo entregó a
la herramienta instrucciones precisas sobre qué construir, qué implementar, los
patrones de diseño a aplicar (Strategy, Observer, Capa de Servicio, Inyección de
Dependencias) y la arquitectura definida previamente, respetando la secuencia de los
diagramas elaborados por el equipo. El código generado fue tratado como un borrador
sujeto a revisión, no como entregable final.

### Responsabilidad del equipo

El equipo asume la responsabilidad total del contenido entregado, independientemente
de si fue generado con apoyo de IA. Todo el código fue analizado críticamente,
adaptado al contexto del proyecto y comprendido por los integrantes, quienes están
en capacidad de explicar y justificar cualquier parte del sistema ante el docente.