#!/usr/bin/python
# This scrip prepares the database entries for MCCE pKa database.
# Requires an empty flag file db.submit to submit.

import requests
import uuid
import xml.etree.ElementTree as ET

from os import stat
from pwd import getpwuid

#def find_owner(filename):
#    return getpwuid(stat(filename).st_uid).pw_name


ph2Kcal = 1.364
Kcal2kT = 1.688
submitfile = "pka.sub"

URL_HEAD = "http://www.rcsb.org/pdb/rest/"

class MACROMOL:
    def __init__(self):
        self.chains=[]
        self.taxonomy=""
        self.name=""
        return

def get_pdb_records(line):
    a = {}
    line = line.strip()
    s = line.split(None, 1)
    string = s[1].strip()

    while (string.find('=') > 0):
        i = string.find('=')
        key = string[:i].strip()
        string = string[i + 1:].strip()
        i = string.find('"')
        string = string[i + 1:].strip()
        i = string.find('"')
        value = string[:i].strip()
        string = string[i + 1:].strip()
        a[key] = value
    return a


class CONFORMER:
    def __init__(self):
        self.name = ""
        self.flag = ""
        self.crg = 0.0
        self.Em0 = 0.0
        self.pKa0 = 0.0
        self.ne = 0
        self.nH = 0
        return


class RESIDUE:
    def __init__(self):
        self.name = ""
        self.seq = 0
        self.cid = " "
        self.icode = " "
        self.ionizable = False
        self.titrated = False
        self.pKaOff = True
        self.sign = ""
        self.pKa = 0.0
        self.slope = 0.0
        self.chi2 = 0.0
        self.conf = []
        self.groundconf = []
        self.chargedconf = []
        return


class PROTEIN:
    def __init__(self):
        self.pI = -999.0
        self.titration_range = []
        self.titration_type = ""
        self.residues = []
        self.PI = "Out of range"

        lines = open("run.prm").readlines()
        fname_extra=""
        for line in lines:
            if line.find("(EXTRA)") >= 0:
                fname_extra = line.split()[0]
            elif line.find("(EPSILON_PROT)") >= 0:
                self.epsilon = int(float(line.split()[0]))
        self.scale_ele = 1.0
        self.scale_vdw = 1.0 / self.epsilon
        self.scale_vdw0 = 1.0 / self.epsilon
        self.scale_vdw1 = 1.0 / self.epsilon
        self.scale_tors = 1.0 / self.epsilon
        self.scale_dsolv = 1.0

        if fname_extra:
            lines = open(fname_extra).readlines()
            for line in lines:
                fields = line.split()
                if len(fields) != 3:
                    continue
                elif fields[0] + fields[1] == "SCALINGVDW0":
                    self.scale_vdw0 = float(fields[2])
                elif fields[0] + fields[1] == "SCALINGVDW1":
                    self.scale_vdw1 = float(fields[2])
                elif fields[0] + fields[1] == "SCALINGVDW":
                    self.scale_vdw = float(fields[2])
                elif fields[0] + fields[1] == "SCALINGTORS":
                    self.scale_tors = float(fields[2])
                elif fields[0] + fields[1] == "SCALINGELE":
                    self.scale_ele = float(fields[2])
                elif fields[0] + fields[1] == "SCALINGDSOLV":
                    self.scale_dsolv = float(fields[2])
        return

    def read_headlst(self):
        # This function reads in head3.lst and compose the complete residue list
        # read_pKout() further mark ionizable residues and assign ionization properties
        lines = open("head3.lst").readlines()
        lines.pop(0)
        for line in lines:
            res = line[6:9]
            cid = line[11]
            seq = int(line[12:16])
            icode = line[16]

            new_conf = CONFORMER()
            new_conf.name = line[6:20]
            new_conf.fl = line[21]
            new_conf.hocc = float(line[22:27])
            new_conf.crg = float(line[27:34])
            new_conf.Em0 = float(line[34:40])
            new_conf.pK0 = float(line[40:46])
            new_conf.ne = int(line[46:49])
            new_conf.nH = int(line[49:52])
            new_conf.vdw0 = float(line[52:60]) * self.scale_vdw0
            new_conf.vdw1 = float(line[60:68]) * self.scale_vdw1
            new_conf.tors = float(line[68:76]) * self.scale_tors
            new_conf.epol = float(line[76:84]) * self.scale_ele
            new_conf.dsol = float(line[84:92]) * self.scale_dsolv
            new_conf.extra = float(line[92:100])
            new_conf.self = new_conf.vdw0 + new_conf.vdw1 + new_conf.tors + new_conf.epol + new_conf.dsol + new_conf.extra
            resid = (res, cid, seq, icode)
            new_res = True
            for residue in self.residues:
                if residue.resid == resid:
                    new_res = False
                    break
            if new_res:
                new_residue = RESIDUE()
                new_residue.conf.append(new_conf)
                new_residue.resid = resid
                new_residue.name = res
                new_residue.cid = cid
                new_residue.seq = seq
                new_residue.icode = icode
                self.residues.append(new_residue)
            else:
                residue.conf.append(new_conf)
        return


    def read_fort38(self):
        lines = open("fort.38").readlines()
        temp = lines[0].split()
        ttype = temp[0].strip().upper()

        if ttype == 'PH':
            self.titration_type = 'pH'
            self.titration_unit = 'pH'
        else:
            print "Error: This program does not process Eh titration."
            sys.exit(1)
        self.titration_range = [float(x) for x in temp[1:]]
        lines.pop(0)

        for line in lines:
            fields = line.split()
            Found = False
            for residue in self.residues:
                for conformer in residue.groundconf:
                    if conformer.name == fields[0]:
                        conformer.occ = [float(x) for x in fields[1:]]
                        Found = True
                    if Found: break
                for conformer in residue.chargedconf:
                    if conformer.name == fields[0]:
                        conformer.occ = [float(x) for x in fields[1:]]
                        Found = True
                    if Found: break
                if Found: break
            if not Found:
                print "Conformer %s in fort.38 was not found in head3.lst" % fields[0]
                sys.exit(1)

        return

    def group_states(self):
        # Group conformers into ground state and charged state.
        for residue in self.residues:
            for conf in residue.conf:
                if conf.name[3] == "+" or conf.name[3] == "-":
                    residue.chargedconf.append(conf)
                    residue.sign = conf.name[3]
                else:
                    residue.groundconf.append(conf)
            # Flag ionizable residues
            if len(residue.groundconf) and len(residue.chargedconf):
                residue.ionizable = True
        return

    def read_pKout(self):
        # This function mark ionizable residues and assign ionization properties
        lines = open("pK.out").readlines()
        line = lines.pop(0)  # remove the title line
        fields = line.split()
        if fields[0] == "pH":
            self.titration_type = "pH"
        elif fields[0] == "Eh":
            self.titration_type = "Eh"
        for line in lines:
            res = line[:10]
            content = line[10:]
            name = res[:3]
            sign = res[3]
            cid = res[4]
            seq = int(res[5:9])
            icode = res[9]
            resid = (name, cid, seq, icode)
            for residue in self.residues:
                # print residue.resid, "<--->", resid
                if residue.resid == resid:
                    if not residue.ionizable:
                        print "Error: pK.out has an ionizable residue %s that is not ionizable in head3.lst" % res
                        sys.exit(1)
                    residue.titrated = True
                    fields = content.split()

                    if fields[0] == "pKa":
                        residue.pKa = "Out of range"
                        residue.slope = -999.0
                        residue.chi2 = -999.0
                    else:
                        residue.pKaOff = False
                        residue.pKa = float(fields[0])
                        residue.slope = float(fields[1])
                        residue.chi2 = float(fields[2]) / 1000.0
        return


    def calc_titration(self):
        self.crg = [0.0 for x in self.titration_range]
        for residue in self.residues:
            residue.crg = [0.0 for x in self.titration_range]
            for conformer in residue.conf:
                for i in range(len(self.titration_range)):
                    charge = conformer.crg * conformer.occ[i]
                    residue.crg[i] += charge
                    self.crg[i] += charge

        # scan the total charge and find if PI is within titration range
        # decide which two columns will be used to get residue mfe
        if self.crg[0] * self.crg[len(self.titration_range) - 1] < 0.0:
            for i in range(len(self.titration_range) - 1):
                if self.crg[i] * self.crg[i + 1] < 0.0:
                    d1 = abs(self.crg[i])
                    d2 = abs(self.crg[i + 1])
                    self.PI = d1 / (d1 + d2) * (self.titration_range[i + 1] - self.titration_range[i]) + \
                              self.titration_range[i]
        else:
            self.PI = "Out of range"

        return

    def read_pdbfile(self, pdbfile):
        chain_ids = []
        models=[]
        lines = open(pdbfile).readlines()
        for line in lines:
            if line[:6] == "ATOM  " or line[:6] == "HETATM":
                cid = line[21]
                if not (cid in chain_ids): chain_ids.append(cid)
            elif line[:6] == "MODEL ":
                fields=line.split()
                models.append(fields[1])
        self.chain_ids = chain_ids
        self.models= models
        return


    def print_submitfile(self, pdbfile):
        # Protein level
        # scan the pdb file and collect as much info as possible
        pdb_id = ""
        protein_name = ""
        chain_ids = ",".join(self.chain_ids)
        pka_method = "MCCE"
        structure_method = ""
        resolution = ""
        model = ""
        structure_size = ""

        path = os.getcwd()
        fields = path.split("/")
        dir = fields[len(fields) - 1]
        subfields = dir.split(".")
        pdb_id = subfields[0]

        # Get molecule description
        url = "%sdescribeMol?structureId=%s" % (URL_HEAD, pdb_id)
        result=requests.get(url)
        xml=result.content.encode('ascii','ignore')
        root=ET.fromstring(xml)

        #go to level of <polymer>, find chains, length, macroMolecule, and Taxonomy in each polymer
        polymers = []
        for structureId in root:
            for polymer in structureId:
                # print polymer.attrib

                p=MACROMOL()
                p.length=int(polymer.attrib["length"])
                for child in polymer:
                    if child.tag=='chain':
                        p.chains.append(child.attrib["id"])
                    elif child.tag=='Taxonomy':
                        p.taxonomy=child.attrib["name"]
                    elif child.tag=='macroMolecule':
                        p.name=child.attrib["name"]
                    elif child.tag=='fragment':
                        p.name=child.attrib["desc"]
                polymers.append(p)

        involved_polymers=[]
        for cid in self.chain_ids:
            for p in polymers:
                if cid in p.chains:
                    involved_polymers.append(p)
                    break

        protein_name = []
        taxonomy = []
        length=0
        for p in involved_polymers:
            length += p.length
            if not (p.name in protein_name):
                protein_name.append(p.name)

            if not (p.taxonomy in taxonomy):
                taxonomy.append(p.taxonomy)

        # Get PDB description
        url = "%sdescribePDB?structureId=%s" % (URL_HEAD, pdb_id)
        result=requests.get(url)
        xml=result.content.encode('ascii','ignore')
        root=ET.fromstring(xml)

        for child in root:
            if child.tag=="PDB":
                expmethod=child.attrib["expMethod"]
                resolution=float(child.attrib["resolution"])


        f = open(submitfile, "w")
        f.write("# Mandatory entries in Captial and Optional entries in lower cases.\n")
        f.write("UNIQUEID=%s\n" % str(uuid.uuid4()))
        #f.write("PUBLISH=True\n")
        #f.write("OWNER=%s\n" % find_owner(path))
        f.write("PDB ID=%s\n" % pdb_id)
        f.write("PROTEIN NAME=%s\n" % ",".join(protein_name))
        f.write("taxonomy=%s\n" % ",".join(taxonomy))
        f.write("PKA METHOD=\"MCCE\"\n")
        f.write("epsilon=%.1f\n" % self.epsilon)
        f.write("CHAIN_IDS=%s\n" % chain_ids)
        f.write("STRUCTURE SIZE=%s\n" % length)
        f.write("STRUCTURE METHOD=%s\n" % expmethod)
        f.write("resolution=%s\n" % resolution)
        f.write("model=%s\n" % ",".join(self.models))
        if isinstance(self.PI, str):
            f.write("isoelectric point=%s\n" % self.PI)
        else:
            f.write("isoelectric point=%.2f\n" % self.PI)

        tcurve=["%.1f:%.2f" % (self.titration_range[i], self.crg[i]) for i in range(len(self.titration_range))]
        f.write("protein titration=\"%s\"\n" % ";".join(tcurve))
        f.write("remark=\n")

        for residue in self.residues:
            if residue.pKaOff: continue  # skip if no pKa was titrated
            resname = "%s %c %d" % (residue.name, residue.cid, residue.seq)
            f.write("PKA.%s=\"%.2f\"\n" % (resname, residue.pKa))
            f.write("pKa err.%s=\"%.2f; %.2f\"\n" % (resname, residue.slope, residue.chi2 * 1000))
            #f.write("dsol.%s=%.2f\n" % (resname, residue.dsol))
            tcurve=["%.1f:%.2f" % (self.titration_range[i], residue.crg[i]) for i in range(len(self.titration_range))]
            f.write("pKa titration.%s=\"%s\"\n" % (resname, ";".join(tcurve)))
        f.close()
        return


if __name__ == "__main__":
    import sys, os, subprocess

    protein = PROTEIN()
    protein.read_headlst()
    protein.group_states()

    # For each ionizable residues, load its conformers and group them with ionization states
    protein.read_pKout()
    protein.read_fort38()

    # calculate titration curve and PI
    protein.calc_titration()

    # Print database submit file
    lines = open("run.prm").readlines()
    for line in lines:
        if line.find("(INPDB)") > 0:
            fields = line.split()
            pdbfile = fields[0]
            break
    protein.read_pdbfile(pdbfile)

    protein.print_submitfile(pdbfile)


    # do mfe and pairwise
    lines=open("pK.out").readlines()
    lines.pop(0)
    for line in lines:
        residue = line.split()[0]
        print "Calling mfesub.py for residue %s ..." % residue,
        #outputlines=subprocess.Popen(["mfesub.py", residue], stdout=subprocess.PIPE).stdout.readlines()
        sp=subprocess.Popen(["mfesub.py", residue], stdout=subprocess.PIPE)
        if sp.returncode:
            "Failed, aborted."
            sys.exit(1)
        else:
            print "Success."
            outputlines=sp.stdout.readlines()
            open(submitfile, "a").writelines(outputlines)




















   
   
   
