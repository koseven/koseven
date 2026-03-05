# Security Policy

## Supported Versions

Koseven is a community-maintained fork of the Kohana 3.3.x framework. Security updates are provided on a **best-effort, volunteer-driven** basis.

| Version | Supported          |
|---------|--------------------|
| 3.3.x   | :white_check_mark: |
| < 3.3   | :x:                |

**Note**:  
Only the latest code on the `master` branch (and the most recent tagged release) receives security fixes. We strongly recommend staying on the latest available 3.3.x release or tracking the `master` branch. Older tagged releases (e.g. 3.3.9 and earlier) are no longer actively supported.

## Reporting a Vulnerability

We take all security vulnerabilities seriously and appreciate responsible disclosure.

**Please do not report security issues through public GitHub issues** until a fix is available.

### Preferred method (recommended)
1. Go to the **[Security tab](https://github.com/koseven/koseven/security)** of the repository.  
2. Click **“Report a vulnerability”**.  

This creates a **private** security advisory that only the maintainers can see.

### Alternative methods
- Open a new issue with the title prefix `**[SECURITY]**` (we will convert it to a private advisory if needed).  
- Contact active maintainers via recent commit authors or the “New maintainers” discussion (see open issues).

### What to include in your report
- Description of the vulnerability  
- Affected Koseven version(s)  
- Steps to reproduce (with a minimal example if possible)  
- Expected vs. actual behavior  
- Potential impact (e.g. XSS, SQL injection, remote code execution, information disclosure)  
- Any suggested fix or mitigation  

### What you can expect
- **Acknowledgment** of your report within **7 days** (volunteer project).  
- Regular updates on investigation and progress.  
- If accepted, a fix will be prepared and released as quickly as possible (usually in the next patch release).  
- You will be credited in the release notes and security advisory unless you request anonymity.  

If the vulnerability is declined, we will explain the reasoning.

---

Thank you for helping keep Koseven secure!
