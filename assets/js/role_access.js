const roleAccessMap = {
  Admin: {
    isAccess: true,
    accessClass: [
      "all",
      "filter-access",
      "accept-reject-btn",
      "parent_cat_access",
    ],
    exceptionClass: ["preview-mode"],
  },
  Manager: {
    isAccess: true,
    accessClass: [
      "all",
      "filter-access",
      "accept-reject-btn",
      "parent_cat_access",
    ],
    exceptionClass: ["preview-mode"],
  },
  "SEO Manager": {
    isAccess: true,
    accessClass: [
      "canonical_link",
      "seo-all-container",
      "attire-access",
      "status",
      "special_id",
      "keyword-id",
      "virtual-id",
      "submit-btn",
      "filter-access",
      "special-page-id",
      "assign-seo",
      "seo-submit",
      "no-index",
      "parent_cat_access",
      "check-primary-density-btn",
      "check-density-btn",
      "new_cat_id_item",
      "accept-reject-btn",
    ],
    exceptionClass: [
      "seo-access-container",
      "preview-mode",
      "designer-access-container",
      "designer-employee-container",
      "disable-container",
    ],
  },
  "SEO Executive": {
    isAccess: true,
    accessClass: ["all", "filter-access", "attire-access"],
    exceptionClass: [
      "preview-mode",
      "canonical_link",
      "designer-access-container",
      "designer-employee-container",
      "assign-seo",
      "disable-container",
    ],
  },
  "SEO Intern": {
    isAccess: true,
    accessClass: ["all", "filter-access"],
    exceptionClass: [
      "canonical_link",
      "status",
      "designer-access-container",
      "designer-employee-container",
      "assign-seo",
      "preview-mode",
      "disable-container",
    ],
  },
  "Designer Manager": {
    isAccess: true,
    accessClass: ["all", "attire-access"],
    exceptionClass: [
      "seo-access-container",
      "seo-all-container",
      "designer-employee-container",
      "preview-mode",
      "disable-container",
    ],
  },
  "Designer Employee": {
    isAccess: true,
    accessClass: ["all"],
    exceptionClass: [
      "seo-access-container",
      "seo-all-container",
      "preview-mode",
      "disable-container",
    ],
  },
};

// console.log(roleKey);

$(document).ready(function () {
  applyRoleAccess(roleKey);
  hideLoadingScreen();
});

function hideLoadingScreen() {
  const loadingScreen = document.getElementById("main_loading_screen");
  if (loadingScreen) {
    loadingScreen.style.display = "none";
  }

  const tagsInputContainer = document.querySelector(".bootstrap-tagsinput");
  if (tagsInputContainer) {
    const tagsInput = tagsInputContainer.querySelector('input[type="text"]');
    if (tagsInput) {
      Object.assign(tagsInput, {
        autocomplete: "on",
        list: "related_tag_list",
      });

      Object.assign(tagsInput.style, {
        width: "100%",
        height: "45px",
        border: "1px solid #000000",
        borderRadius: "5px",
        marginTop: "5px",
      });
    }
  }
}

function isEditButton(el) {
  const text = (el.textContent || "").trim().toLowerCase();
  const classList = el.classList || [];
  return (
    el.classList.contains("edit") ||
    el.classList.contains("close") ||
    el.classList.contains("edit-btn")
  );
}

function applyRoleAccess(roleKey) {
  const roleAccess = roleAccessMap[roleKey];
  const exceptionClass = roleAccess.exceptionClass || [];
  const accessClass = roleAccess.accessClass || [];

  exceptionClass.forEach((className) => {
    document.querySelectorAll("." + className).forEach((container) => {
      const elements = container.matches("input, select, textarea, button, a")
        ? [container]
        : container.querySelectorAll("input, select, textarea, button, a");

      let accessClassCount = 0;

      elements.forEach((el) => {
        try {
          const hasAccessClass = [...el.classList].some((cls) =>
            accessClass.includes(cls)
          );

          if (hasAccessClass) {
            accessClassCount++;
          }

          if (
            !isEditButton(el) &&
            !el.closest(".filter-access") &&
            !hasAccessClass &&
            !el.classList.contains("csrf")
          ) {
            if (!el.disabled) {
              el.setAttribute("disabled", "");

              const tag = el.tagName.toLowerCase();
              const type = el.getAttribute("type")?.toLowerCase();

              if (
                tag === "button" ||
                (tag === "input" && (type === "submit" || type === "button"))
              ) {
                el.style.cursor = "not-allowed";
              }
            }
          }
        } catch (e) {
          console.error(e);
        }
      });
    });
  });
}

// function applyRoleAccess(roleKey) {
//     const roleAccess = roleAccessMap[roleKey];
//     const allFields = document.querySelectorAll(
//         "input, select, textarea, button, a,div"
//     );
//
//     const accessClass = roleAccess.accessClass || [];
//     const exceptionClass = roleAccess.exceptionClass || [];
//
//
//     if (!roleAccess || !roleAccess.isAccess) {
//         allFields.forEach((el) => {
//             // if (!isEditButton(el)) el.disabled = true;
//             if (!isEditButton(el)) el.setAttribute("disabled","");
//         });
//         return;
//     }
//     // const accessClass = roleAccess.accessClass || [];
//     // const exceptionClass = roleAccess.exceptionClass || [];
//     if (!accessClass.includes("all")) {
//         allFields.forEach((el) => {
//             // if (!isEditButton(el)) el.disabled = false;
//             if (!isEditButton(el)) el.setAttribute("disabled","");
//         });
//     }
//     accessClass.forEach((className) => {
//         if (className === "all") return;
//         document.querySelectorAll("." + className).forEach((el) => {
//             if (el.matches("input, select, textarea, button, a")) {
//                 // if (!isEditButton(el)) el.disabled = false;
//                 if (!isEditButton(el)) el.removeAttribute("disabled");
//             } else {
//                 el.querySelectorAll("input, select, textarea, button, a").forEach(
//                     (child) => {
//                         if (!isEditButton(child)) child.removeAttribute("disabled");
//                     }
//                 );
//             }
//         });
//     });
//     exceptionClass.forEach((className) => {
//         document.querySelectorAll("." + className).forEach((el) => {
//             const allowInside = accessClass.some((ac) => {
//                 const selector = `.${ac}, .${ac} *`;
//                 return el.querySelector(selector) !== null;
//             });
//             if (allowInside) return;
//             if (el.matches("input, select, textarea, button, a")) {
//                 if (!isEditButton(el)) el.setAttribute("disabled","");
//             } else {
//                 el.querySelectorAll("input, select, textarea, button, a").forEach(
//                     (child) => {
//                         // if (!isEditButton(child)) child.disabled = true;
//                         if (!isEditButton(child)) child.setAttribute("disabled","");
//                     }
//                 );
//             }
//         });
//     });
// }
