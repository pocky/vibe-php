# Methodology Comparison

**Evolution: PRD-Plan-Act → Spec-Driven → Unified**

## Comparison Table

| Feature | PRD-Plan-Act | Spec-Driven | Unified |
|---------|--------------|-------------|---------|
| Business Focus | ✅ High | ⚪ Medium | ✅ High |
| Technical Precision | ⚪ Medium | ✅ High | ✅ High |
| Requirements | User Stories | EARS | Both |
| Approval Gates | ❌ None | ✅ Explicit | ✅ Flexible |
| Risk Assessment | ⚪ Basic | ✅ Full | ✅ Full |
| Learning Curve | ✅ Low | ❌ High | ⚪ Medium |

## Command Mapping

| Legacy | Unified | Changes |
|--------|---------|---------|
| `/prd` | `/spec:prd` | +EARS, +gates |
| `/plan` | `/spec:plan` | +risk, +design |
| `/act` | `/spec:act` | +TDD structure |
| `/qa` | `/qa` | No change |

## When to Use

- **PRD-Plan-Act**: Small features, prototypes, familiar teams
- **Spec-Driven**: Complex systems, compliance, large teams
- **Unified** (recommended): Everything else

## Migration

**From PRD-Plan-Act**:
1. Keep using familiar commands (auto-mapped)
2. Add EARS format gradually
3. Introduce approval gates

**From Spec-Driven**:
1. Add business context
2. Merge requirements into PRD
3. Keep approval structure

## Quick Start

```bash
# Simple feature
/prd blog categories    # Auto uses spec:prd
/plan blog             # Auto uses spec:plan
/act                   # Auto uses spec:act
/qa

# Complex feature
/spec:prd payment gateway
/spec:plan payment
/spec:advanced         # Security analysis
/spec:act
/qa
```

**Key**: Unified combines best of both. Use aliases for easy transition.