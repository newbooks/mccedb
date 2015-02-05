#!/usr/bin/python
"Convert pka.sub to mysql file pka.sql"


class RESIDUE:
    def __init__(self):
        self.uniqueid=""
        self.resname=""
        self.cid=""
        self.seq=""
        self.pka=""
        self.pka_err=""
        self.pka_titartion=""

class PROTEIN:
    def __init__(self):
        self.uniqueid=""
        self.pdb_id=""
        self.protein_name=""
        self.taxonomy=""
        self.pka_method=""
        self.epsilon=""
        self.chain_ids=""
        self.structure_size=""
        self.structure_method=""
        self.resolution=""
        self.model=""
        self.isoelectric_point=""
        self.protein_titration=""
        self.remark=""
        self.mfelines=[]
        self.pwlines=[]
        return

    def load(self, fname):
        lines = open(fname).readlines()
        self.residues = {}
        for line in lines:
            line = line.strip()
            fields = line.split('#')
            line = fields[0]
            fields = line.split('=')
            if len(fields) < 2: continue
            key = fields[0].strip().upper().replace(" ", "_")
            value = fields[1].strip().strip('"').strip()

            # examine key. If key is two fields separated by ".", then it belongs to residue
            keyfields=key.split(".")

            if len(keyfields) == 1:
                if key=="UNIQUEID":
                    self.uniqueid=value
                elif key=="PDB_ID":
                    self.pdb_id=value
                elif key=="PROTEIN_NAME":
                    self.protein_name=value
                elif key=="TAXONOMY":
                    self.taxonomy=value
                elif key=="PKA_METHOD":
                    self.pka_method=value
                elif key=="EPSILON":
                    self.epsilon=value
                elif key=="CHAIN_IDS":
                    self.chain_ids=value
                elif key=="STRUCTURE_SIZE":
                    self.structure_size=value
                elif key=="STRUCTURE_METHOD":
                    self.structure_method=value
                elif key=="RESOLUTION":
                    self.resolution=value
                elif key=="MODEL":
                    self.model=value
                elif key=="ISOELECTRIC_POINT":
                    self.isoelectric_point=value
                elif key=="PROTEIN_TITRATION":
                    self.protein_titration=value
                elif key=="REMARK":
                    self.remark=value
                elif key=="MFE":
                    self.mfelines.append(value)
                elif key=="PAIRWISE":
                    self.pwlines.append(value)
                else:
                    print "Error! Can not interpret key %s." % key
            elif len(keyfields) == 2: #Residue info
                p_key = keyfields[0].strip().upper()
                s_key = keyfields[1].strip()  # no upper case conversion. CID and cofactor may be in lower case.
                if not self.residues.has_key(s_key):
                    self.residues[s_key] = RESIDUE()
                    self.residues[s_key].uniqueid = self.uniqueid
                    fields = s_key.split("_")
                    self.residues[s_key].residue = fields[0]
                    self.residues[s_key].cid = fields[1]
                    self.residues[s_key].seq = fields[2]
                if p_key == "PKA":
                    self.residues[s_key].pka = value
                elif p_key == "PKA_ERR":
                    self.residues[s_key].pka_err = value
                elif p_key == "PKA_TITRATION":
                    self.residues[s_key].pka_titration = value
                else:
                    print "Error! Can not interpret key %s." % p_key
            else: # not defined yet
                print "Error! Can not interpret key %s" % key

        return

    def write(self, fname):
        lines = ["SET autocommit=0;\n"]
        lines.append("DELETE FROM proteins WHERE UNIQUEID='%s';\n" % self.uniqueid)
        lines.append("DELETE FROM residues WHERE UNIQUEID='%s';\n" % self.uniqueid)
        lines.append("DELETE FROM mfe WHERE UNIQUEID='%s';\n" % self.uniqueid)
        lines.append("DELETE FROM pairwise WHERE UNIQUEID='%s';\n" % self.uniqueid)

        lines.append(
            "INSERT INTO proteins VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s', '%s');\n" %
            (self.uniqueid, self.publish, self.owner, self.pdb_id, self.protein_name, self.taxonomy, \
             self.pka_method, self.epsilon, self.chain_ids, self.structure_size, self.structure_method, self.resolution, \
             self.model, self.isoelectric_point,self.protein_titration, self.remark))

        for key in self.residues.keys():
            residue = self.residues[key]
            lines.append(
                "INSERT INTO residues VALUES ('%s','%s','%s','%s','%s','%s','%s','%s');\n" %
                (residue.uniqueid, residue.resname, residue.cid, residue.seq, residue.pka, residue.pka_err, \
                 residue.pka_titration, residue.dsol))

        for line in self.mfelines:
            kv={}
            fields=line.split(";")
            for field in fields:
                pair = field.split(":")
                key = pair[0]
                value=pair[1]
                kv[key]=value
            lines.append(
                "INSERT INTO mfe VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s');\n" %
                (self.uniqueid, kv["PH"], kv["RESNAME"], kv["CID"], kv["SEQ"], kv["VDW0"], kv["VDW1"], kv["TORS"],\
                 kv["EBKB"], kv["DSOL"], kv["PHPK"], kv["NegTS"],kv["OFFSET"], kv["TOTALPW"]))
            kv.clear()

        for line in self.pwlines:
            kv={}
            fields=line.split(";")
            for field in fields:
                pair = field.split(":")
                key = pair[0]
                value=pair[1]
                kv[key]=value
            lines.append(
                "INSERT INTO pairwise VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s');\n" %
                (self.uniqueid, kv["PH"], kv["RESNAME"], kv["CID"], kv["SEQ"], kv["RESNAME2"], kv["CID2"], kv["SEQ2"],\
                 kv["PAIRWISE"], kv["CHARGE"]))
            kv.clear()

        line.append("COMMIT;\n")
        open(fname, "w").writelines(lines)
        return


if __name__ == "__main__":
    prot = PROTEIN()
    prot.load("pka.sub")
    prot.write("pka.sql")
