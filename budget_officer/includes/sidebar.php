<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav nav flex-column" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-grid"></i>
                <span>DASHBOARD</span>
            </a>
        </li>

        <li class="nav-heading mt-3">Financial Management</li>

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#status-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bar-chart"></i><span>STATUS OF FUND</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="status-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <!-- Personnel Services Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#personal-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>PERSONNEL SERVICES</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="personal-nav" class="nav-content collapse" data-bs-parent="#status-nav">
                        <li>
                            <a href="oo1_personalServices.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo1_personalServices.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>001-Personnel Services</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2_tida.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo2_tida.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>002-Tida Contractual</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- MOOE Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#mooe-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>MOOE</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="mooe-nav" class="nav-content collapse" data-bs-parent="#status-nav">
                        <li>
                            <a href="gas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gas.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>GAS-General Administration and Support</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo1.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo1.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO1-Exports and Investment Program</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo2.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO2-Industry Development Program</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3-MSME Development Program</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_1.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_1.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3.1-Negosyo Centers</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_2.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_2.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3.2-OTOP Next Gen</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_3.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_3.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3.3-Shared Service Facilities</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_1.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo4_1_1.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO4.1.1-Monitoring and Enforcement</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_2.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo4_1_2.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO4.1.2-Accreditation and Issuance of BN</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- CARP Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#carp-fund-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>CARP</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="carp-fund-nav" class="nav-content collapse" data-bs-parent="#status-nav">
                        <li>
                            <a href="oo3_carp.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_carp.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3-Carp</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- RAPID Growth Project Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#rapid-fund-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>RAPID GROWTH PROJECT</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="rapid-fund-nav" class="nav-content collapse" data-bs-parent="#status-nav">
                        <li>
                            <a href="o1_rapidRO12.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'o1_rapidRO12.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>O1-Rapid RO 12</span>
                            </a>
                        </li>
                        <li>
                            <a href="o2_rapid.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'o2_rapid.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>O2-Rapid</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#obligation-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bar-chart"></i><span>OBLIGATION</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="obligation-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <!-- Personnel Services Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#personal-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>PERSONNEL SERVICES</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="personal-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="oo1_personalServicesObligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo1_personalServicesObligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO1-Personnel Services</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2_tidaObligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo2_tidaObligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO2-Tida Contractual</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- MOOE Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#mooe-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>MOOE</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="mooe-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="gas_obligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gas_obligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>GAS-General Administration and Support</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo1_obligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo1_obligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO1-Exports and Investment Program</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2_obligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo2_obligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO2-Industry Development Program</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_obligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_obligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3-MSME Development Program</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_1_obligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_1_obligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3.1-Negosyo Centers</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_2_obligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_2_obligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3.2-OTOP Next Gen</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_3_obligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_3_obligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3.3-Shared Service Facilities</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_1_obligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo4_1_1_obligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO4.1.1-Monitoring and Enforcement</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_2_obligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo4_1_2_obligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO4.1.2-Accreditation and Issuance of BN</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- CARP Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#carp-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>CARP</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="carp-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="oo3_carpObligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_carpObligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3-Carp</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- RAPID Growth Project Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#rapid-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>RAPID GROWTH PROJECT</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="rapid-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="o1_rapidRO12Obligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'o1_rapidRO12Obligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>O1-Rapid RO 12</span>
                            </a>
                        </li>
                        <li>
                            <a href="o2_rapidObligation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'o2_rapidObligation.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>O2-Rapid</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="nav-heading mt-3">Reports & Analytics</li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#reports-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bar-chart"></i><span>REPORTS</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="reports-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <!-- Summary Report -->
                <li>
                    <a href="budgetOfficerSummaryReport.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'budgetOfficerSummaryReport.php' ? 'active' : ''; ?>">
                        <i class="bi bi-file-earmark-bar-graph"></i><span>SUMMARY REPORT</span>
                    </a>
                </li>

                <!-- Personnel Services Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#personal-report-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>PERSONNEL SERVICES</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="personal-report-nav" class="nav-content collapse" data-bs-parent="#reports-nav">
                        <li>
                            <a href="oo1_personnelReport.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo1_personnelReport.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>001-Personnel Services Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2_tidaReport.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo2_tidaReport.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>002-Tida Contractual Report</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- MOOE Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#mooe-report-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>MOOE</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="mooe-report-nav" class="nav-content collapse" data-bs-parent="#reports-nav">
                        <li>
                            <a href="gasReport.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gasReport.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>GAS-General Administration and Support Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo1Report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo1Report.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO1-Exports and Investment Program Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2Report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo2Report.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO2-Industry Development Program Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3Report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3Report.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3-MSME Development Program Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_1Report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_1Report.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3.1-Negosyo Centers Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_2Report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_2Report.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3.2-OTOP Next Gen Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_3Report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_3Report.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3.3-Shared Service Facilities Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_1Report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo4_1_1Report.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO4.1.1-Monitoring and Enforcement Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_2Report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo4_1_2Report.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO4.1.2-Accreditation and Issuance of BN Report</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- CARP Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#carp-report-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>CARP</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="carp-report-nav" class="nav-content collapse" data-bs-parent="#reports-nav">
                        <li>
                            <a href="oo3_carpReport.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oo3_carpReport.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>OO3-Carp Report</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- RAPID Growth Project Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#rapid-report-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>RAPID GROWTH PROJECT</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="rapid-report-nav" class="nav-content collapse" data-bs-parent="#reports-nav">
                        <li>
                            <a href="o1_rapid12Report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'o1_rapid12Report.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>O1-Rapid RO 12 Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="o2_rapidReport.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'o2_rapidReport.php' ? 'active' : ''; ?>">
                                <i class="bi bi-circle"></i><span>O2-Rapid Report</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
    </ul>
</aside>

<!-- Optional script section -->
<script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
<script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
<script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
<script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
<script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>
<script src="../NiceAdmin/assets/js/main.js"></script>