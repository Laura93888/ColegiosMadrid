---
name: PHP preview workflow
description: Replit-specific constraints for serving this PHP project in Preview.
---

The PHP app must be served by a webview workflow on `0.0.0.0:5000`; the `.replit` port mapping should use local port 5000 as well.

**Why:** Replit webview workflows require port 5000, while an imported PHP project may only have a port mapping and no configured workflow. Replacing `.replit` through the validated configuration flow can reset the workflow definitions, so the workflow must be recreated afterward if that happens.

**How to apply:** When the preview is unavailable, check that `Start application` runs `php -S 0.0.0.0:5000 -t .`, waits for port 5000, and uses `outputType = "webview"`. After changing `.replit`, re-check and recreate the workflow if necessary.