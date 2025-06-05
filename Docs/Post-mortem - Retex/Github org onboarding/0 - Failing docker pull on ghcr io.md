# Post-Mortem: GHCR User Onboarding Issue

**Date:** 2025-06-05  
**Repository:** VerticalAsso/VerticalWpCustomContent  
**Registry:** GitHub Container Registry (ghcr.io)  
**Participants:** @bebenlebricolo, new developer

## Summary

A new team member was unable to pull private Docker images from the organization’s GitHub Container Registry. Despite having team membership and generating a Personal Access Token (PAT), authentication consistently failed. The issue was eventually traced to organization-level PAT expiration policy and insufficient explicit team permissions.

## Timeline

- **T0:** New user added to GitHub organization, granted read/write access to repos.
- **T1:** User generated a classic PAT with `read:packages` and `write:packages` scopes, set to "never expire".
- **T2:** User attempted Docker authentication (`docker login ghcr.io`) and subsequent image pull, which failed.
- **T3:** Troubleshooting steps followed:
    - Confirmed correct PAT scopes and Docker login syntax.
    - Verified repo and package permissions.
    - Checked for SSO requirements (not applicable).
- **T4:** Investigation revealed:
    - Organization imposed a max 90-day PAT lifetime, user’s “never expire” token was invalid for org resources.
    - User was in the org’s “People” list but not in a team with explicit role/permissions.
- **T5:** Resolved by:
    - Creating a new PAT with a 90-day expiration.
    - Adding the user to a “Development” team with explicit member role and read/write permissions.
- **T6:** User successfully authenticated and pulled images after changes.

## Root Causes

1. **PAT Expiration Policy Conflict:**  
   The organization enforced a maximum PAT lifetime, conflicting with the user’s attempt to use a non-expiring token. GitHub silently blocks such tokens for org resources.

2. **Insufficient Explicit Team Permissions:**  
   Being listed as a “Person” in the organization does not grant package or repo access—explicit team membership and permissions are required for private registry access.

## Lessons Learned

- Organization-level security and access policies can silently override individual user settings, causing confusing errors.
- Package and repo access for GHCR is tightly bound to explicit team permissions, not general org membership.

## Action Items

- **Documentation:**  
  Update internal onboarding docs to:
  - Specify PAT expiration requirements in accordance with org policy.
  - Require explicit team assignment and permission review for all new developers.

- **Automation:**  
  Consider automating onboarding checks for:
    - PAT policy compliance.
    - Team assignments on joining the org.

- **Communication:**  
  Share this post-mortem with all current and future team leads to ensure smoother onboarding.

## References

- [GitHub Docs: Managing PAT policies](https://docs.github.com/en/enterprise-cloud@latest/authentication/keeping-your-account-and-data-secure/managing-personal-access-tokens#setting-a-personal-access-token-policy-for-your-organization)
- [GitHub Docs: Managing team access to organization packages](https://docs.github.com/en/packages/learn-github-packages/setting-permissions-for-packages#about-permissions-for-packages-in-organizations)
- [GitHub Docs: Working with the Container registry](https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry)
