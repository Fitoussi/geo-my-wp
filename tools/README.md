# GEO my WP development tools

Install the pinned development dependencies independently of the plugin runtime:

```sh
composer --working-dir=tools install
composer --working-dir=tools phpcs
composer --working-dir=tools phpcompat
```

`tools/phpcs.xml.dist` is the authoritative WordPress ruleset; the root ruleset
forwards to it. `tools/phpcompat.xml.dist` checks the declared PHP 7.4+ baseline.
Both scan the main plugin file, `includes/` and `plugins/`, excluding bundled
third-party `includes/libraries/` code. Findings are existing source debt, not
permission to reformat the plugin. Do not run a whole-plugin PHPCBF pass.

VS Code uses these local binaries for diagnostics on save. Automatic fixing is
disabled to preserve reviewed release source. The local ignored
`geo-my-wp.code-workspace` includes this checkout and WordPress core definitions.
The committed `.vscode/` files also work when the plugin is opened directly.

The root Composer manifest and lock contain only the existing runtime dependency
metadata. Obsolete root development requirements were removed; use the isolated
`tools/` manifest and lock for all PHP code-quality dependencies. The root runtime
dependency pin was preserved during that cleanup.

The root npm build command currently refers to a missing `build.js`. No build
or run-on-save command is wired to it. Restoring the legacy asset build pipeline
requires a separate scoped change and asset-output verification. The unused
legacy Node dependencies were removed during development advisory cleanup;
there is currently no active Node build/watch dependency set.

These development files and `tools/vendor/` are excluded from the existing
allowlisted production packaging process. The verified 4.5.5.7 ZIP is unchanged.

## Initial baseline (October 22, 2026)

The installed rules scan 216 maintained PHP files. WPCS reports 3,065 errors and
729 warnings; no automatic fixes were applied. PHPCompatibilityWP reports 51
errors against the declared PHP 7.0+ minimum. A comparison with public 4.5.5.5
found the same 51 findings (same files, checks and messages; line positions can
shift). PHP 7.4+ compatibility scanning reports zero errors or warnings.

These compatibility findings predate 4.5.5.7 and include function-call trailing
commas requiring PHP 7.3. Do not silently raise the declared plugin minimum or
rewrite runtime code as part of setting up tools; resolve the support-policy
mismatch separately with the owner.

## PHP minimum update (October 22, 2026)

The owner approved raising the plugin minimum to PHP 7.4. Plugin metadata,
Composer platform metadata, editor indexing and both development rulesets now
use 7.4. The initial 7.0 scan above is retained as historical evidence; the
current PHP 7.4+ compatibility check passes without changing runtime syntax.
