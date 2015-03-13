#!/usr/bin/python
import sys

class CONFORMER:
    def __init__(self):
        self.occ = []

class E_IONIZE:
    def __init__(self):
        self.mfe = []

# Global variables and default values
ph1 = 0.0
eh1 = 0.0
scale_ele = 1.0
scale_vdw = 1.0
scale_vdw0 = 1.0
scale_vdw1 = 1.0
scale_tors = 1.0
scale_dsolv = 1.0
fname_extra = ""
ph2Kcal = 1.364
mev2Kcal = 0.0235
Kcal2kT = 1.688
conformers = []
residues = []
titration_range = []
titration_type = ""

#special residue whose groud state and excited state are defined here:
#              RES  : ground  excited
Special_res = {"_CU": (["+1"], ["+2"]),
               "UbQ": (["01"], ["-1"])}


def first_ph():
    global ph1, eh1, scale_ele, scale_vdw, scale_vdw0, scale_vdw1, scale_tors, scale_dsolv, fname_extra
    lines = open("run.prm").readlines()
    for line in lines:
        if line.find("(TITR_PH0)") >= 0:
            ph1 = float(line.split()[0])
        elif line.find("(TITR_EH0)") >= 0:
            eh1 = float(line.split()[0])
        elif line.find("(EXTRA)") >= 0:
            fname_extra = line.split()[0]
        elif line.find("(EPSILON_PROT)") >= 0:
            epsilon = int(float(line.split()[0]))
        elif line.find("(SCALE_ELE)") >= 0:
            scale_ele = float(line.split()[0])
        elif line.find("(SCALE_VDW)") >= 0:
            scale_vdw = float(line.split()[0])
            scale_vdw0 = float(line.split()[0])
            scale_vdw1 = float(line.split()[0])

    scale_ele = 1.0
    scale_vdw = 1.0 / epsilon
    scale_vdw0 = 1.0 / epsilon
    scale_vdw1 = 1.0 / epsilon
    scale_tors = 1.0 / epsilon
    scale_dsolv = 1.0

    if fname_extra:
        lines = open(fname_extra).readlines()
        for line in lines:
            fields = line.split()
            if len(fields) != 3:
                continue
            elif fields[0] + fields[1] == "SCALINGVDW0":
                scale_vdw0 = float(fields[2])
            elif fields[0] + fields[1] == "SCALINGVDW1":
                scale_vdw1 = float(fields[2])
            elif fields[0] + fields[1] == "SCALINGVDW":
                scale_vdw = float(fields[2])
            elif fields[0] + fields[1] == "SCALINGTORS":
                scale_tors = float(fields[2])
            elif fields[0] + fields[1] == "SCALINGELE":
                scale_ele = float(fields[2])
            elif fields[0] + fields[1] == "SCALINGDSOLV":
                scale_dsolv = float(fields[2])
    return


def read_headlst():
    global conformers
    global titration_range, titration_type, titration_unit, scale_ele, scale_vdw

    lines = open("head3.lst").readlines()
    lines.pop(0)  # remove the title line

    if len(conformers) > 0:
        print "WARNING: adding to non empty conformer list."

    for line in lines:
        fields = line.split()
        conformer = CONFORMER()
        conformer.id = fields[1]
        conformer.fl = fields[2]
        conformer.hocc = float(fields[3])  # occ in head3.lst
        conformer.crg = float(fields[4])
        conformer.Em0 = float(fields[5])
        conformer.pKa0 = float(fields[6])
        conformer.ne = int(fields[7])
        conformer.nH = int(fields[8])
        conformer.vdw0 = float(fields[9]) * scale_vdw0
        conformer.vdw1 = float(fields[10]) * scale_vdw1
        conformer.tors = float(fields[11]) * scale_tors
        conformer.epol = float(fields[12]) * scale_ele
        conformer.dsolv = float(fields[13]) * scale_dsolv
        conformer.extra = float(fields[14])
        conformer.self = conformer.vdw0 + conformer.vdw1 + conformer.tors + conformer.epol + conformer.dsolv + conformer.extra
        conformers.append(conformer)
    return


def read_fort38():
    global conformers
    global titration_range, titration_type, titration_unit

    #lines = [x for x in open("fort.38").readlines() if x.find("DM") == -1]
    lines = open("fort.38").readlines()

    temp = lines[0].split()
    ttype = temp[0].strip().upper()
    if ttype == 'EH':
        titration_type = 'Eh'
        titration_unit = 'mV'
    elif ttype == 'PH':
        titration_type = 'pH'
        titration_unit = 'pH'
    else:
        titration_type = ttype
        titration_unit = '?'
    titration_range = [float(x) for x in temp[1:]]
    lines.pop(0)

    for i in range(len(conformers)):
        line = lines[i].split()
        if conformers[i].id != line[0]:
            print "ERROR, %s in head3.lst doesn't match %s in fort.38" % (conformers[i].id, line[0])
            return
        conformers[i].occ = [float(x) for x in line[1:]]

    return


def group_residues():
    global conformers
    global titration_range, titration_type, titration_unit, residues


    # group conformers into residues
    residues_list={}

    for conformer in conformers:
        resid = (conformer.id[:3], conformer.id[5:11])
        if residues_list.has_key(resid):
            residues_list[resid].append(conformer)
        else:
            residues_list[resid] = [conformer]

    resids = residues_list.keys()
    resids.sort(key=lambda x: x[1])

    for resid in resids:
        residue = [resid, [], []]
        for conformer in residues_list[resid]:
            if Special_res.has_key(conformer.id[:3]):
                if conformer.id[3:5] in Special_res[conformer.id[:3]][0]:
                    residue[1].append(conformer)
                elif conformer.id[3:5] in Special_res[conformer.id[:3]][1]:
                    residue[2].append(conformer)
                else:
                    print "This conformer was defined as special chargeable but without 2 states."
            else:
                if conformer.id[3] == '0' or conformer.id[3] == 'D':
                    residue[1].append(conformer)
                else:
                    residue[2].append(conformer)
        residues.append(residue)
    return


def read_pK():
    lines = open("pK.out").readlines()
    pK = [[line[:10], line[10:]] for line in lines]
    return pK


def E_ionize(res_pKa_name):
    global residues
    import math

    res_name = res_pKa_name[:3], res_pKa_name[4:]
    found = 0
    for residue in residues:
        if res_name == residue[0]:
            found = 1
            break

    if found == 0:
        print "Residue %s %s not found in fort.38" % res_name
        sys.exit(0)

    dG_ionize = E_IONIZE()

    # mfe for each conformer
    for conformer in residue[1] + residue[2]:
        conformer.pHeffect = [0.0 for i in range(len(titration_range))]
        conformer.Eheffect = [0.0 for i in range(len(titration_range))]
        conformer.res_mfe = [[0.0 for i in range(len(titration_range))] for x in residues]
        conformer.mfe_total = [0.0 for i in range(len(titration_range))]
        conformer.E_total = [0.0 for i in range(len(titration_range))]

        # pairwise energy table
        pairwise = {}
        if conformer.id.find("DM") < 0:
            lines = open("energies/" + conformer.id + ".opp").readlines()
            for line in lines:
                line = line.split()
                if len(line) >= 3:
                    # check the vdw clash
                    if line[3].find("999.000") >= 0:
                        pairwise[line[1]] = 999.000
                    else:
                        if len(line[3]) > 8:
                            line[3] = line[3][:(len(line[3]) - 8)]
                        pairwise[line[1]] = float(line[2]) * scale_ele + float(line[3]) * scale_vdw

        # make conformer mfe
        for i in range(len(titration_range)):
            point = titration_range[i]
            conf_mfe = [0.0 for x in titration_range]

            # pH effect in Kcal/mol
            if titration_type == 'pH':
                conformer.pHeffect[i] = (point - conformer.pKa0) * conformer.nH * ph2Kcal
            else:
                conformer.pHeffect[i] = (ph1 - conformer.pKa0) * conformer.nH * ph2Kcal

            # Eh effect in Kcal/mol
            if titration_type == 'Eh':
                conformer.Eheffect[i] = (point - conformer.Em0) * conformer.ne * mev2Kcal
            else:
                conformer.Eheffect[i] = (eh1 - conformer.Em0) * conformer.ne * mev2Kcal

            for j in range(len(residues)):
                res = residues[j]
                if res[0] == residue[0]:
                    mfe = 0.0
                else:
                    mfe = 0.0
                    for conf in res[1] + res[2]:
                        if not pairwise.has_key(conf.id): pairwise[conf.id] = 0.0
                        mfe += pairwise[conf.id] * conf.occ[i]
                # This mfe is at 1 titration point, from one residue
                conformer.res_mfe[j][i] = mfe
                conformer.mfe_total[i] += mfe


            # update conformer E_total
            conformer.E_total[i] = conformer.mfe_total[i] \
                                   + conformer.pHeffect[i] \
                                   + conformer.Eheffect[i] \
                                   + conformer.self

    E_totals_min = [min(x.E_total) for x in residue[1]+residue[2]]
    Eref=min(E_totals_min)

    # Calculate mfe occupancy of each conformer
    SigmaE = [0.0 for i in range(len(titration_range))]
    for i in range(len(titration_range)):
        Ei = [math.exp(-(conformer.E_total[i] - Eref) * Kcal2kT) for conformer in residue[1] + residue[2]]
        for x in Ei: SigmaE[i] += x

    for conformer in residue[1] + residue[2]:
        conformer.rocc = [0.0 for x in titration_range]
        for i in range(len(titration_range)):
            if conformer.fl.upper() == 'T':
                #print conformer.hocc
                if conformer.hocc < 0.001:
                    conformer.rocc[i] = 0.0
                elif conformer.hocc > 0.999:
                    conformer.rocc[i] = 1000.0 / SigmaE[i]  # 1000 times more occupied than the lowest
                else:
                    print "Error: partial occupancy was assigned in head3.lst"
                    sys.exit()
            else:
                conformer.rocc[i] = math.exp(-(conformer.E_total[i] - Eref) * Kcal2kT) / SigmaE[i]  # recovered occ

    SigmaOcc = [0.0 for i in range(len(titration_range))]
    for conformer in residue[1]:
        for i in range(len(titration_range)): SigmaOcc[i] += conformer.rocc[i]
    for conformer in residue[1]:
        conformer.nocc = []
        for i in range(len(titration_range)):
            if SigmaOcc[i] < 1.0E-25 and conformer.rocc[i] < 1.0E-25:
                conformer.nocc.append(1.0)
                #print "Assign 1.0"
            else:
                conformer.nocc.append(conformer.rocc[i] / SigmaOcc[i])
                #print "calculated"

    SigmaOcc = [0.0 for i in range(len(titration_range))]
    for conformer in residue[2]:
        for i in range(len(titration_range)): SigmaOcc[i] += conformer.rocc[i]
    for conformer in residue[2]:
        conformer.nocc = []
        for i in range(len(titration_range)):
            if SigmaOcc[i] < 1.0E-25 and conformer.rocc[i] < 1.0E-25:
                conformer.nocc.append(1.0)
            else:
                conformer.nocc.append(conformer.rocc[i] / SigmaOcc[i])


    # energy terms of ground state
    ground_state = E_IONIZE()
    ground_state.vdw0 = [0.0 for x in titration_range]
    ground_state.vdw1 = [0.0 for x in titration_range]
    ground_state.tors = [0.0 for x in titration_range]
    ground_state.epol = [0.0 for x in titration_range]
    ground_state.dsolv = [0.0 for x in titration_range]
    ground_state.extra = [0.0 for x in titration_range]
    ground_state.pHeffect = [0.0 for x in titration_range]
    ground_state.Eheffect = [0.0 for x in titration_range]
    ground_state.mfe_total = [0.0 for x in titration_range]
    ground_state.res_mfe = [[0.0 for x in titration_range] for x in residues]
    ground_state.E_total = [0.0 for x in titration_range]
    ground_state.TS = [0.0 for x in titration_range]
    for conformer in residue[1]:
        for i in range(len(titration_range)):
            ground_state.vdw0[i] += conformer.nocc[i] * conformer.vdw0
            ground_state.vdw1[i] += conformer.nocc[i] * conformer.vdw1
            ground_state.tors[i] += conformer.nocc[i] * conformer.tors
            ground_state.epol[i] += conformer.nocc[i] * conformer.epol
            ground_state.dsolv[i] += conformer.nocc[i] * conformer.dsolv
            ground_state.extra[i] += conformer.nocc[i] * conformer.extra
            ground_state.pHeffect[i] += conformer.nocc[i] * conformer.pHeffect[i]
            ground_state.Eheffect[i] += conformer.nocc[i] * conformer.Eheffect[i]
            ground_state.mfe_total[i] += conformer.nocc[i] * conformer.mfe_total[i]
            ground_state.E_total[i] += conformer.nocc[i] * conformer.E_total[i]
            if conformer.nocc[i] > 0.000001:
                ground_state.TS[i] += -conformer.nocc[i] * math.log(conformer.nocc[i]) / Kcal2kT
        for j in range(len(residues)):
            for i in range(len(titration_range)):
                ground_state.res_mfe[j][i] += conformer.nocc[i] * conformer.res_mfe[j][i]

    ground_state.G = [ground_state.E_total[i] - ground_state.TS[i] for i in range(len(titration_range))]

    # energy terms of charged state
    charged_state = E_IONIZE()
    charged_state.vdw0 = [0.0 for x in titration_range]
    charged_state.vdw1 = [0.0 for x in titration_range]
    charged_state.tors = [0.0 for x in titration_range]
    charged_state.epol = [0.0 for x in titration_range]
    charged_state.dsolv = [0.0 for x in titration_range]
    charged_state.extra = [0.0 for x in titration_range]
    charged_state.pHeffect = [0.0 for x in titration_range]
    charged_state.Eheffect = [0.0 for x in titration_range]
    charged_state.res_mfe = [[0.0 for x in titration_range] for x in residues]
    charged_state.mfe_total = [0.0 for x in titration_range]
    charged_state.E_total = [0.0 for x in titration_range]
    charged_state.TS = [0.0 for x in titration_range]
    for conformer in residue[2]:
        for i in range(len(titration_range)):
            charged_state.vdw0[i] += conformer.nocc[i] * conformer.vdw0
            charged_state.vdw1[i] += conformer.nocc[i] * conformer.vdw1
            charged_state.tors[i] += conformer.nocc[i] * conformer.tors
            charged_state.epol[i] += conformer.nocc[i] * conformer.epol
            charged_state.dsolv[i] += conformer.nocc[i] * conformer.dsolv
            charged_state.extra[i] += conformer.nocc[i] * conformer.extra
            charged_state.pHeffect[i] += conformer.nocc[i] * conformer.pHeffect[i]
            charged_state.Eheffect[i] += conformer.nocc[i] * conformer.Eheffect[i]
            charged_state.mfe_total[i] += conformer.nocc[i] * conformer.mfe_total[i]
            charged_state.E_total[i] += conformer.nocc[i] * conformer.E_total[i]
            if conformer.nocc[i] > 0.000001:
                charged_state.TS[i] += -conformer.nocc[i] * math.log(conformer.nocc[i]) / Kcal2kT
        for j in range(len(residues)):
            for i in range(len(titration_range)):
                charged_state.res_mfe[j][i] += conformer.nocc[i] * conformer.res_mfe[j][i]

    charged_state.G = [charged_state.E_total[i] - charged_state.TS[i] for i in range(len(titration_range))]

    dG_ionize.ground_state = ground_state
    dG_ionize.charged_state = charged_state
    dG_ionize.ground_confs = residue[1]
    dG_ionize.charged_confs = residue[2]
    dG_ionize.resID = residue[0]

    return dG_ionize

def read_sumcrg():
    sumcrg={}
    lines = open('sum_crg.out', 'r').readlines()
    for line in lines:
        fields=line.split()
        resid=fields[0][:3],fields[0][4:10]
        crg=[float(x) for x in fields[1:]]
        sumcrg[resid]=crg
    return sumcrg

if __name__ == '__main__':

    if (len(sys.argv) < 2):
        print "mfesub.py res_id [pH_cutoff]"
        print "   res_id:          The residue ID in pK.out"
        print "   pH_cutoff:       display pairwise interaction bigger than this value, default 0.1"
        sys.exit(0)

    pH_cutoff = 0.1 # default value
    if len(sys.argv) > 2:
        pH_cutoff = float(sys.argv[2])

    # read run.prm
    first_ph()

    #read head list
    read_headlst()

    # read pK.out
    pK = read_pK()

    # read fort.38
    read_fort38()

    group_residues()

    dG = E_ionize(sys.argv[1])
    sumcrg = read_sumcrg()

    for i in range(len(titration_range)):
        dG_point = E_IONIZE()
        dG_point.vdw0 = dG.charged_state.vdw0[i] - dG.ground_state.vdw0[i]
        dG_point.vdw1 = dG.charged_state.vdw1[i] - dG.ground_state.vdw1[i]
        dG_point.tors = dG.charged_state.tors[i] - dG.ground_state.tors[i]
        dG_point.epol = dG.charged_state.epol[i] - dG.ground_state.epol[i]
        dG_point.dsolv = dG.charged_state.dsolv[i] - dG.ground_state.dsolv[i]
        dG_point.extra = dG.charged_state.extra[i] - dG.ground_state.extra[i]
        dG_point.pHeffect = dG.charged_state.pHeffect[i] - dG.ground_state.pHeffect[i]
        dG_point.Eheffect = dG.charged_state.Eheffect[i] - dG.ground_state.Eheffect[i]
        dG_point.mfe_total = dG.charged_state.mfe_total[i] - dG.ground_state.mfe_total[i]
        dG_point.E_total = dG.charged_state.E_total[i] - dG.ground_state.E_total[i]
        dG_point.TS = dG.charged_state.TS[i] - dG.ground_state.TS[i]

        for j in range(len(dG.charged_state.res_mfe)):
            dG_point.mfe.append(dG.charged_state.res_mfe[j][i] - dG.ground_state.res_mfe[j][i])
        dG_point.G = dG.charged_state.G[i] - dG.ground_state.G[i]

        for x in pK:
            if (sys.argv[1][:3] == x[0][:3] and sys.argv[1][4:] == x[0][4:]):
                res_pKa = x[1].split()[0]


        myresid = sys.argv[1][:3], sys.argv[1][4:]
        if sumcrg.has_key(myresid):
            ion_state = sumcrg[myresid][i]
        else:
            print "No charge found %s" % sys.argv[1]
            ion_state = 0.0

        print "MFE=PH:%.2f;RESNAME:%s;CID:%c;SEQ:%d;VDW0:%.2f;VDW1:%.2f;TORS:%.2f;EBKB:%.2f;DSOL:%.2f;PHPK:%.2f;"\
            "NegTS:%.2f;OFFSET:%.2f;TOTALPW:%.2f;CHARGE:%.2f" % (
               titration_range[i], sys.argv[1][:3], sys.argv[1][4], int(sys.argv[1][5:9]),\
               dG_point.vdw0 / ph2Kcal, dG_point.vdw1 / ph2Kcal, dG_point.tors / ph2Kcal,\
               dG_point.epol / ph2Kcal, dG_point.dsolv / ph2Kcal, dG_point.pHeffect / ph2Kcal,\
               -dG_point.TS / ph2Kcal, dG_point.extra / ph2Kcal, dG_point.mfe_total / ph2Kcal, ion_state)


        for j in range(len(dG_point.mfe)):
            if abs(dG_point.mfe[j] / ph2Kcal) > pH_cutoff:
                if sumcrg.has_key(residues[j][0]):
                    ion_state = sumcrg[(residues[j][0])][i]
                else:
                    ion_state = 0.0

                print "PAIRWISE=PH:%.2f;RESNAME:%s;CID:%c;SEQ:%d;RESNAME2:%s;CID2:%c;SEQ2:%d;PAIRWISE:%.2f;CHARGE:%.2f" % \
                (titration_range[i], sys.argv[1][:3], sys.argv[1][4], int(sys.argv[1][5:9]),\
                 residues[j][0][0],residues[j][0][1][0], int(residues[j][0][1][1:5]),\
                 dG_point.mfe[j] / ph2Kcal, float(ion_state))

