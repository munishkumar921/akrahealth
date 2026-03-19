export const doctorNavOptions = [
    { id: "fa-solid fa-calendar-plus", color: "#1be1b3", label: "Appointments", path: "doctor.schedule.index" },
    { id: "fa-solid fa-money", color: "#800080", label: "Financial", path: "doctor.finance.bills_to_submit"},
    { id: "fa-solid fa-video", color: "#FF0000", label: "Live", path: "doctor.triage.index"},
    { id: "fa-solid fa-stethoscope", color: "#00CFDE", label: "Encounter", path: "doctor.encounters.index"},
    { id: "fa-solid fa-headset", color: "#007BFF", label: "Schedule", path: "doctor.todays.call"},
];

export const assistantNavOptions = [
    { id: "fa-solid fa-calendar-plus", color: "#1be1b3", label: "Appointments", path: "assistant.schedule.index" },
    { id: "fa-solid fa-money", color: "#800080", label: "Financial", path: "assistant.finance.bills_to_submit"},
    { id: "fa-solid fa-video", color: "#FF0000", label: "Live", path: "triage.index"},
    { id: "fa-solid fa-stethoscope", color: "#00CFDE", label: "Encounter", path: "assistant.encounters.index"},
    { id: "fa-solid fa-headset", color: "#007BFF", label: "Schedule", path: "assistant.todays.call"},
];

// For backward compatibility, keep navOptions pointing to doctorNavOptions
export const navOptions = doctorNavOptions;

// These are made svg as the icons needed were not a part of bootstrap icons
export const officeOptions = [
    {
        label: "Vaccines",
        svg: `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24">
	            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m17 3l4 4m-2-2l-4.5 4.5m-3-3l6 6m-1-1L10 18H6v-4l6.5-6.5m-5 5L9 14m1.5-4.5L12 11M3 21l3-3" />
            </svg>
        `,
        path: "doctor.vaccines.index",
    },
    {
        label: "Suppliments",
        svg: `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24">
	        <g fill="none" stroke="currentColor" stroke-width="1.5">
	        	<path d="M3 10c0-3.771 0-5.657 1.172-6.828S7.229 2 11 2h2c3.771 0 5.657 0 6.828 1.172S21 6.229 21 10v4c0 3.771 0 5.657-1.172 6.828S16.771 22 13 22h-2c-3.771 0-5.657 0-6.828-1.172S3 17.771 3 14z" />
	        	<path stroke-linecap="round" d="M12 6v2m0 0v2m0-2h-2m2 0h2m-6 6h8m-7 4h6" />
	        </g>
        </svg>
        `,
        path: "doctor.supplements.index",
    },
    {
        label: "Reports",
        svg: `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 32 32">
	            <path fill="currentColor" d="M15 20h2v4h-2zm5-2h2v6h-2zm-10-4h2v10h-2z" />
	            <path fill="currentColor" d="M25 5h-3V4a2 2 0 0 0-2-2h-8a2 2 0 0 0-2 2v1H7a2 2 0 0 0-2 2v21a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2M12 4h8v4h-8Zm13 24H7V7h3v3h12V7h3Z" />
            </svg>`,
        path: "doctor.reports.index",
    },
];

export const configOptions = [
    {
        label: "Address Book",
        svg: `
           <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 512 512">
	            <path fill="currentColor" d="M496 144.768v-33.536h-39.232V42a25 25 0 0 0-25.179-24.768H80.411A25 25 0 0 0 55.232 42v430a25 25 0 0 0 25.179 24.768h351.178A25 25 0 0 0 456.768 472v-71.232H496v-33.536h-39.232v-94.464H496v-33.536h-39.232v-94.464Zm-72.768 94.464H376v33.536h47.232v94.464H376v33.536h47.232v62.464H88.768V50.768h334.464v60.464H376v33.536h47.232Z" />
	            <path fill="currentColor" d="m313.639 306.925l-28.745-18.685l13.82-33.655v-52.871a65.714 65.714 0 1 0-131.428 0v52.557l12.721 34.684l-27.646 17.97A48.97 48.97 0 0 0 130 348.129V400h206v-51.871a48.97 48.97 0 0 0-22.361-41.204M304 368H162v-19.871a17.08 17.08 0 0 1 7.8-14.373l49.033-31.872l-19.547-53.3v-46.87a33.714 33.714 0 0 1 67.428 0v46.557l-21.5 52.347l50.986 33.138a17.08 17.08 0 0 1 7.8 14.373Z" />
            </svg>
        `,
        path: "doctor.configure.address_book",
    },
    {
        label: "My Forms",
        svg: `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24">
            	<path fill="currentColor" d="M14.727 6h6l-6-6zm0 .727H14V0H4.91c-.905 0-1.637.732-1.637 1.636v20.728c0 .904.732 1.636 1.636 1.636h14.182c.904 0 1.636-.732 1.636-1.636V6.727zM7.91 17.318a.819.819 0 1 1 .001-1.638a.819.819 0 0 1 0 1.638zm0-3.273a.819.819 0 1 1 .001-1.637a.819.819 0 0 1 0 1.637zm0-3.272a.819.819 0 1 1 .001-1.638a.819.819 0 0 1 0 1.638zm9 6.409h-6.818v-1.364h6.818zm0-3.273h-6.818v-1.364h6.818zm0-3.273h-6.818V9.273h6.818z" />
            </svg>
        `,
        path: "doctor.configure.my_forms",
    },
    {
        label: "Provider Exceptions",
        svg: `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 2048 2048">
            	<path fill="currentColor" d="M1920 128v1792H0V128h384V0h128v128h896V0h128v128zM128 256v256h1664V256h-256v128h-128V256H512v128H384V256zm1664 1536V640H128v1152zm-440-768l-241 189l101 315l-252-197l-252 197l101-315l-241-189h302l90-280l90 280z" />
            </svg>
        `,
        path: "doctor.configure.provider_exceptions",
    },
    {
        label: "Schedule Setup",
        svg: `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24">
            	<path fill="currentColor" d="M7 3V1h2v2h6V1h2v2h4a1 1 0 0 1 1 1v5h-2V5h-3v2h-2V5H9v2H7V5H4v14h6v2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm10 9a4 4 0 1 0 0 8a4 4 0 0 0 0-8m-6 4a6 6 0 1 1 12 0a6 6 0 0 1-12 0m5-3v3.414l2.293 2.293l1.414-1.414L18 15.586V13z" />
            </svg>
        `,
        path: "doctor.configure.schedule_setup",
    },
    {
        label: "Visit Types",
        svg: `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 64 64">
            	<path fill="currentColor" d="M25.1 23.6c0 4 3.2 7.2 7.2 7.2s7.2-3.2 7.2-7.2s-3.2-7.2-7.2-7.2c-3.9-.1-7.2 3.2-7.2 7.2m10.5 0c0 1.8-1.5 3.2-3.2 3.2s-3.2-1.5-3.2-3.2s1.5-3.2 3.2-3.2s3.2 1.4 3.2 3.2" />
            	<path fill="currentColor" d="M8.1 31.7c.5 0 .9-.2 1.2-.4l7.1-5.6c.9-.7 1-1.9.3-2.8s-1.9-1-2.8-.3l-3.6 2.9C12.9 15.3 21.9 8.2 32.4 8.2S52 15.3 54.6 25.5c.3 1.1 1.4 1.7 2.4 1.4c1.1-.3 1.7-1.4 1.4-2.4c-3-12-13.8-20.4-26.1-20.4c-12.2 0-22.8 8.2-25.9 20L4.1 21c-.7-.9-1.9-1.1-2.8-.4S.2 22.5.9 23.4l5.6 7.5c.3.4.8.7 1.3.8zm54.7 13.1l-3.9-8.5c-.2-.5-.6-.9-1.2-1.1c-.5-.2-1.1-.1-1.6.1l-8.1 4c-1 .5-1.4 1.7-.9 2.7s1.7 1.4 2.7.9l3.9-1.9a22.9 22.9 0 0 1-9.2 11.3v-9.4c0-5.1-3.8-9.3-8.5-9.3h-7.7c-4.7 0-8.5 4.2-8.5 9.4v9c-4.2-2.8-7.4-6.9-9-11.9c-.3-1-1.5-1.6-2.5-1.3S6.7 40.3 7 41.3c3.6 11 13.8 18.4 25.4 18.4c11.1 0 21-6.8 25-17.1l1.7 3.8c.3.7 1.1 1.2 1.8 1.2c.3 0 .6-.1.8-.2c1.1-.4 1.5-1.6 1.1-2.6m-39 9.3V42.9c0-3 2-5.4 4.5-5.4h1.8v6.9c0 1.1.9 2 2 2s2-.9 2-2v-6.9H36c2.5 0 4.5 2.4 4.5 5.3v11.4c-2.6 1-5.3 1.5-8.2 1.5c-3 .1-5.9-.5-8.5-1.6" />
            </svg>
        `,
        path: "doctor.configure.visit_type",
    },
];
export const patientNavOptions = [
    { id: "fa-solid fa-calendar-plus", color: "#1be1b3", label: "Schedule", path: "patient.book.appointment" },
    { id: "fa-solid fa-stethoscope", color: "#06C270", label: "Encounter", path: "patient.encounters" },
];
export const profileOptions = [
    {
        label: "Profile",
        svg: `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24">
	            <g fill="currentColor" fill-rule="evenodd" clip-rule="evenodd">
		            <path d="M16 9a4 4 0 1 1-8 0a4 4 0 0 1 8 0m-2 0a2 2 0 1 1-4 0a2 2 0 0 1 4 0" />
		            <path d="M12 1C5.925 1 1 5.925 1 12s4.925 11 11 11s11-4.925 11-11S18.075 1 12 1M3 12c0 2.09.713 4.014 1.908 5.542A8.99 8.99 0 0 1 12.065 14a8.98 8.98 0 0 1 7.092 3.458A9 9 0 1 0 3 12m9 9a8.96 8.96 0 0 1-5.672-2.012A6.99 6.99 0 0 1 12.065 16a6.99 6.99 0 0 1 5.689 2.92A8.96 8.96 0 0 1 12 21" />
	            </g>
            </svg>
        `,
        path: "",
    },
    {
        label: "Messages",
        svg: `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24">
	            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8m-8 4h6m4-9a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3h-5l-5 3v-3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3z" />
            </svg>
        `,
        path: "",
    },
    {
        label: "Change Password",
        svg: `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 48 48">
	            <path fill="currentColor" d="M4 13.25A6.25 6.25 0 0 1 10.25 7h27.5A6.25 6.25 0 0 1 44 13.25v15.264a11 11 0 0 0-2.5-1.554V13.25a3.75 3.75 0 0 0-3.75-3.75h-27.5a3.75 3.75 0 0 0-3.75 3.75v15.5a3.75 3.75 0 0 0 3.75 3.75h13.654l2.5 2.5H10.25A6.25 6.25 0 0 1 4 28.75zM33.25 25c-.301 0-.578-.107-.794-.285a3.2 3.2 0 0 0-.44-1.171A1.25 1.25 0 0 1 33.25 22.5h4.5a1.25 1.25 0 1 1 0 2.5zm-6.482-4l1.244 1.244a3.2 3.2 0 0 0-1.06.708l-.884.884L25 22.768l-1.866 1.866a1.25 1.25 0 0 1-1.768-1.768L23.232 21l-1.866-1.866a1.25 1.25 0 0 1 1.768-1.768L25 19.232l1.866-1.866a1.25 1.25 0 0 1 1.768 1.768zm-16.402-3.634a1.25 1.25 0 0 1 1.768 0L14 19.232l1.866-1.866a1.25 1.25 0 0 1 1.768 1.768L15.768 21l1.866 1.866a1.25 1.25 0 0 1-1.768 1.768L14 22.768l-1.866 1.866a1.25 1.25 0 0 1-1.768-1.768L12.232 21l-1.866-1.866a1.25 1.25 0 0 1 0-1.768m19.768 8.768a1.25 1.25 0 0 0-1.768-1.768l-4 4a1.25 1.25 0 0 0 0 1.768l4 4a1.25 1.25 0 0 0 1.768-1.768L28.268 30.5H37a6.5 6.5 0 1 1-6.415 7.553a1.25 1.25 0 1 0-2.468.402C28.813 42.734 32.524 46 37 46a9 9 0 0 0 0-18h-8.732z" />
            </svg>
        `,
        path: "",
    },
];
