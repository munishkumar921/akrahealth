<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Cta from '@/Partials/Cta.vue';
import HeroSection2 from '@/Partials/HeroSection2.vue';
import ListItems from '@/Partials/ListItems.vue';
import IconCard from '@/Partials/IconCard.vue';
import IconboxCard from '@/Partials/IconboxCard.vue';
import DemoRequest from '@/Pages/Modals/DemoRequest.vue';
import emrIntegration from '@/Pages/Modals/EmrIntegrationModal.vue';

defineProps({
  policy: String,
});

</script>

<template>
  <AppLayout title="EMR Integration"  meta-title="EMR Integration" 
    description="Discover seamless EMR integration for healthcare practices using FHIR and HL7 standards. Streamline workflows, enable data interoperability, ensure HIPAA-compliant data security, and boost efficiency with AkraHealth.">
    <HeroSection2
    title="Smart EMR Integration with FHIR & HL7 Standards" 
    description="Connect healthcare systems using FHIR and HL7–based EMR integration to enable secure data exchange, streamline workflows, and support HIPAA-compliant interoperability across your organization."
    bannerImage="/images/screens/dash/ENCOUNTER.webp" 
    >
    <template v-slot:button>
      <a href="#" data-toggle="modal" data-target="#emr-integration-modal" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Get Your Customized EMR </a> 
    </template>
   </HeroSection2>  

    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
            <div class="section-title">
                <h2>EMR Integration Solutions We Can Offer</h2>
            </div>
            <IconCard :items="[
              { title: 'Customizable EMR Templates:',
                description: 'Personalize templates to enhance documentation efficiency across healthcare EMR systems, improving structured data capture and clinical workflows',
                icon: '/images/svg/customizable-emr-templates.svg'
              },
              {
                title: 'Order Entry',
                description: 'Simplify medication, lab, and procedure orders using interoperable EMR workflows.',
                icon: '/images/svg/order-entry.svg'
              },
              {
                title: '24/7 Communication',
                description: 'Enable real-time collaboration across healthcare EMR ecosystems, improving care coordination through secure and interoperable data exchange.',
                icon: '/images/svg/efficient-communication.svg'
              },
              {
                title: 'E-Prescriptions (E-Rx)',
                description: ' Streamline medication workflows within EMR systems, supporting secure prescription exchange aligned with FHIR and HL7 standards.',
                icon: '/images/svg/e-prescriptions-icon-erx.svg'
              },
              {
                title: 'Patient Portal',
                description: ' White-label portals enabling patients to securely access healthcare records while maintaining HIPAA compliance and strong data security.',
                icon: '/images/svg/patient-portal.svg'
              },
              {
                title: 'Lab Integration',
                description: 'Enable real-time lab result updates using HL7 and FHIR-based data interoperability between diagnostic systems and EMRs.',
                icon: '/images/svg/patient-portal.svg'
              },
              {
                title: 'Automated Reminders',
                description: 'AI-driven reminders integrated into healthcare EMR systems for better engagement and continuity of care.',
                icon: '/images/svg/automated-reminders-icon.svg'
              },
              {
                title: 'Medical Speech-to-Text (AI)',
                description: 'Accelerate documentation in EMR software while ensuring protected health information remains secure and compliant.',
                icon: '/images/svg/reporting-analytics.svg'
              },
              // {
              //   title: 'Order Entry',
              //   description: 'Simplify medication, lab, and procedure orders using interoperable EMR workflows.',
              //   icon: '/images/svg/order-entry.svg'
              // }
            ]" />
        </div>
    </section>

       <!-- Call to action section starts here -->
       <Cta 
       title="Medical Billing Integration"
       >
       <template v-slot:button>
            <p>Optimize billing and revenue workflows with secure EMR billing integrations aligned with healthcare compliance standards. Perfect for:</p>
            <ul class="main-list">
                <li>Multi-Specialty Clinics</li>
                <li>Telehealth & Virtual Care Providers</li>
                <li>Hospitals & Healthcare Networks</li>
                <li>Private Practices & Small Clinics</li>
                <li>Healthcare Startups & mHealth App Developers</li>
              </ul>
        </template>
       </Cta>
   <!-- Call to action section ends here -->


   <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="section-title mb-3">
                <h2>How Our EMR Integration Strengthens Healthcare Workflows</h2>
                <p>Built on FHIR and HL7 standards, AkraHealth’s integration framework supports secure data exchange, system interoperability, and scalable healthcare operations.</p>
            </div>
            <IconboxCard :items="[
              { title: 'Seamless Integration with Major EMR Systems', description: 'AkraHealth integrates with leading healthcare EMR platforms such as Epic, Cerner, and NexTech using FHIR APIs and HL7 interfaces, enabling reliable data interoperability across systems.',
               icon: '/images/svg/seamless-integration-with-major-emr-systems.svg' 
              },
              { title: 'Custom EMR Configurations and Workflows', description: 'Tailored EMR configurations that adapt to your healthcare workflows while maintaining interoperability and compliance.',
               icon: '/images/svg/custom-emr-configurations-and-workflows.svg'
              },
              { title: 'Improved Workflow Efficiency', description: 'Real-time synchronization across EMR systems ensures accurate, connected, and efficient healthcare operations.',
                icon: '/images/svg/improved-workflow-efficiency.svg'
              },
              { title: 'Data Security and HIPAA Compliance', description: 'All integrations follow strict HIPAA guidelines, prioritizing data security, encrypted transmission, and controlled access to patient records.',
               icon: '/images/svg/data-security.svg'
              },
              { title: 'Support for Complex Data Migrations', description: 'Secure EMR data migration with preserved integrity, ensuring continuity across healthcare platforms',
               icon: '/images/svg/support-for-complex-data-migrations.svg'
              },
              { title: 'AI-Driven Solutions for Enhanced Documentation', description: 'AI-powered EMR tools reduce administrative burden while maintaining compliance and structured clinical data.',
              icon: '/images/svg/ai-driven-solutions-for-enhanced-documentation.svg'
              },
              // {
              //   title: 'Third-Party Software Integration',
              //   description: ' Create a connected healthcare ecosystem through secure EMR interoperability with CRMs, portals, and telemedicine platforms.',
              //   icon: '/images/svg/support-for-complex-data-migrations.svg'
              // }
            ]" />
            </div>
        </section>

        <!-- <section class="section">
            <div class="container bring-to-front con-box py-0">
              <div class="row justify-content-center">
                <div class="col-lg-9 shadow rounded text-center bg-primary p-5">
                    <div class="text-contrast">
                        <h2 class="mb-3 text-contrast bold fs-20">Book a Personalized Demo!</h2>
                        <p class="font-size-16">Integrate DrChrono, Epic, Cerner, and more with your EMR for a unified healthcare system. Talk to our experts today!</p>
                    </div>
                  <Link  onclick="Calendly.initPopupWidget({url: 'https://calendly.com/akratech-consulting/new-meeting'});return false;" class="btn btn-outline-light btn-rounded mt-2 px-4">Book Your Demo Now</Link>
                </div>
              </div>
            </div>
        </section> -->

  <!-- Why Choose AkraHealth for Your EMR Integration section starts here -->
    <section class="section path-success mt-4">
      <div class="shapes-container">
        <div class="shape-ring absolute right top aos-init aos-animate" data-aos="fade-up"></div>
      </div>

      <div class="container bring-to-front">
        <div class="row g-4 align-items-center text-lg-start">
          <div class="col-md-6 order-md-2">
            <div class="device-twin align-items-center">
              <div class="mockup absolute right aos-init aos-animate" data-aos="fade-right ">
                <div class="screen">
                  <img src="/images/EMR-8.jfif" alt="emr-eight" />
                </div>
                <span class="button"></span>
              </div> 

              <div class="iphone-x front ms-0">
                <div class="screen shadow-box">
                  <img src="/images/screens/app/history.webp" alt="emr-ten" />
                </div>
                <div class="notch"></div>
              </div>
            </div>
          </div>

          <div class="col-md-6 text-md-start">
            <h2 class="bold py-4 text-center text-md-start fs-20">Why Choose AkraHealth for Your EMR Integration?</h2>
            <ListItems :items="[
              { title: 'Smooth Data Flow', description: ' Ensure seamless data interoperability between EMR systems using FHIR and HL7 standards', 
                icon: 'fa-check'
              },
              { title: 'Customization', description: 'Flexible EMR integrations customized for diverse healthcare workflows',
                icon: 'fa-check'
               },
              { title: 'Comprehensive Support', description: 'End-to-end support for EMR integration, optimization, and compliance.', 
                icon: 'fa-check'
              },
              { title: 'HIPAA Compliance', description: 'All solutions are designed with enterprise-grade HIPAA-compliant data security at the core', 
                icon: 'fa-check'
              }

            ]" />
          </div>
        </div>
      </div>
    </section>
    <!-- key Features section ends here -->


    <section class="section">
      <div class="shapes-container overflow-clear">
        <div class="shape shape-circle shape-circle-1">
          <div data-aos="fade-up-left" class="aos-init aos-animate"></div>
        </div>
        <div class="shape shape-circle shape-circle-2">
          <div data-aos="fade-up-right" data-aos-delay="200" class="aos-init"></div>
        </div>
        <div class="shape pattern pattern-dots pattern-dots-1"></div>
      </div>
      <div class="container">
        <div class="row align-items-center gap-y">
          <div class="col-md-6 order-md-last">
              <h2 class="bold pb-3 text-center text-md-start fs-20">EMR Data Migration Services</h2>
              <ListItems :items="[
                { title: 'Migration Plan', 
                description: 'Structured EMR migration strategies ensuring secure healthcare data transfer.', icon: 'fa-check' 
                },
                { title: 'Data Standardization', 
                  description: ' Standardized clinical data using FHIR and HL7 models to improve interoperability.', icon: 'fa-check' 
                },
                { title: 'Providers Coordination', 
                  description: 'Collaborative migration across EMR vendors while maintaining compliance..', 
                  icon: 'fa-check' 
                },
                { title: 'Integrated Reports', 
                  description: ' Unified reporting from interoperable EMR data sources.', icon: 'fa-check' 
                },
                { title: 'Third-party integration', 
                  description: 'Extend healthcare EMR capabilities with secure third-party integrations.', icon: 'fa-check' }
              ]" />
          </div>

          <div class="col-md-6 position-relative">
            <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>
            <div class="iphone-x">
              <div class="screen shadow-box">
                <img src="/images/EMR-15.jfif" alt="emr-fifteen" />
              </div>
              <div class="notch"></div>
            </div>
          </div>
        </div>
      </div>
    </section>


    <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row gap-y align-items-center">
              <div class="col-md-6">
                <div class="mb-4">
                  <h2 class="bold fs-20">AkraHealth & DrChrono: Seamless EMR Integration  </h2>
                  <p>AkraHealth integrates with DrChrono to enhance healthcare data interoperability, enabling secure access, updates, and management of patient data across EMR systems.</p>
                </div>
                <ListItems :items="[
                    { title: 'Secure patient record synchronization', description: 'Securely access, update, and manage patient data within free EMR for physicians, web-based EMR systems, and free online medical records.', icon: 'fa-check' },
                    { title: 'Automated workflows', description: 'Streamline scheduling, billing, and documentation with free medical record software, EMR apps, and free EHR for small practices.', icon: 'fa-check' },
                    { title: 'HIPAA-compliant data exchange', description: 'Maintain HIPAA and APA-compliant data exchange for secure patient management in electronic medical records software free and best free EMR platforms.', icon: 'fa-check' },
                    { title: 'Improved clinical efficiency', description: 'Experience seamless connectivity between DrChrono and other healthcare applications, including telemedicine EMR, E-Rx systems, and white-label patient portals.', icon: 'fa-check' }
                  ]" />

              </div>

              <div class="col-md-6 col-lg-6 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="emr-app screen">
                      <img src="/images/EMR-4.jfif" alt="emr-four" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/EMR-9.webp" alt="emr-nine" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>
           </div>
      </div>
    </section>



    <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row">
          <div class="col-sm-12">
            <div class="section-title">
                <h2>Mirth Connect Integration Solutions</h2>
                <p>AkraHealth delivers advanced HL7 and FHIR-based EMR integrations powered by Mirth Connect, enabling secure healthcare data exchange.</p>
            </div>
          </div>
        </div>
        <div class="row gap-y align-items-center">
              <div class="col-md-6">
                <h2 class="bold fs-20">Our Expertise Includes:</h2>
                <ul class="main-list mt-3">
                  <li><span class="bold">Custom Mirth Connect channel configuration</span>– Configure dedicated channels, connectors, and transformations for seamless EMR/EHR, pharmacy management, and claims adjudication system integrations. This ensures smooth connectivity across web-based EMR systems, free online EMR, and free EHR for small practices.</li>
                  <li><span class="bold">HL7, XML, JSON, and EDI data transformation</span>– Our solutions support NCPDP standard integration for pharmacy claims adjudication, enabling smooth claims processing through EMR small practice solutions and free medical record software.</li>
                  <li><span class="bold">Pharmacy claims and adjudication integrations</span>– Efficient handling of HL7, XML, JSON, and EDI data, ensuring proper exchange across EMR software for healthcare, cloud-based EMR, and electronic medical records software free platforms.</li>
                  <li><span class="bold">Rigorous testing and HIPAA-compliant data security</span>– Rigorous testing and continuous support to ensure compliance with HIPAA and industry regulations for free EHR systems, free software for patient record keeping, and electronic healthcare record systems.</li>
                  <li><span class="bold">Reliable healthcare data interoperability across EMR systems</span>– Improve interoperability for healthcare providers, ensuring accurate and efficient data exchange across EMR apps, telemedicine EMR, E-Rx medicine systems, and white-label patient portals.
                  </li>
                </ul>
              </div>
              <div class="col-md-6 col-lg-6 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="emr-app screen">
                      <img src="/images/EMR-17.jfif" alt="emr-seventeen" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/EMR-16.jfif" alt="emr-sixteen" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>
            </div>
      </div>
    </section>
    <DemoRequest/>
    <emrIntegration/>
  </AppLayout>
</template>
