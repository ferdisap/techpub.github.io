<?php

namespace Tests\Feature\Csdb;

use App\Models\Csdb;
use App\Models\User;
use Database\Factories\CsdbFactory;
use DOMDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;



class CrudTest extends TestCase
{
  public static User $user;
  public static string $filename;
  public static string $xmlstring;

  public function create($assert = true)
  {
    self::$user = User::find(1) ?? User::factory()->create();
    Sanctum::actingAs(self::$user, ["*"]);
    
    $csdbFactory = new CsdbFactory();
    $file = $csdbFactory->generateFile();

    $response = $this->put("/api/s1000d/csdb/create", [
      'xmleditor' => $file[1],
    ]);

    if($assert){
      $response->assertStatus(200);
      self::$filename = $response->json("csdb.filename");
      $this->assertEquals($file[0],self::$filename);
    }
  }

  public function test_create(): void
  {
    $this->create();
  }

  public function test_read():void
  {
    Sanctum::actingAs(self::$user, ["*"]);

    $response = $this->get("/api/s1000d/csdb/read/" . self::$filename);
    $response->assertStatus(200);

    // $message = $response; //
    // $message = $response->content; //
    // $message = $response->assertSee('content');
    // $message = $response->baseResponse->content();
    // var_dump($message);
    
    self::$xmlstring = $response->baseResponse->content();
  }

  public function test_update():void
  {
    Sanctum::actingAs(self::$user, ["*"]);

    $doc = new DOMDocument();
    $doc->loadXML(self::$xmlstring, LIBXML_PARSEHUGE);
    $content = $doc->getElementsByTagName('content')[0];
    $content->remove();

    // var_dump(self::$filename);

    $response = $this->post("/api/s1000d/csdb/update/" . self::$filename, [
      'xmleditor' => $doc->saveXML(),
    ]);

    $response->assertStatus(200);
  }

  public function test_delete() :void
  {
    Sanctum::actingAs(self::$user, ["*"]);

    $response = $this->delete("/api/s1000d/csdb/delete", [
      'filename' => self::$filename,
    ]);
    $response->assertStatus(200);
    
    $response = $this->delete("/api/s1000d/csdb/delete", []);
    $response->assertStatus(412);    
  }

  public function test_restore() :void
  {
    Sanctum::actingAs(self::$user, ["*"]);

    $response = $this->post("/api/s1000d/csdb/restore", [
      'filename' => self::$filename,
    ]);
    $response->assertStatus(200);
    
    $response = $this->post("/api/s1000d/csdb/restore", []);
    $response->assertStatus(412);    
  }

  public function test_permanent_delete() :void
  {
    $this->create(false);

    Sanctum::actingAs(self::$user, ["*"]);

    $response = $this->delete("/api/s1000d/csdb/permanentdelete", [
      'filename' => self::$filename,
    ]);
    $response->assertStatus(200);
    
    $response = $this->delete("/api/s1000d/csdb/permanentdelete", []);
    $response->assertStatus(412);    
  }

  public static function dmodule($usedtd = true)
  {
    $dtd = $usedtd ? '<!DOCTYPE dmodule []>' : '';
    $declaration = '<?xml version="1.0" encoding="utf-8"?>';
    $dmodule = '';
    $dmodule .= $declaration;
    $dmodule .= $dtd;
    $dmodule .=  <<<EOD
      <dmodule xsi:noNamespaceSchemaLocation="http://www.s1000d.org/S1000D_5-0/xml_schema_flat/brex.xsd" xmlns:dc="http://www.purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <identAndStatusSection>
          <dmAddress>
            <dmIdent>
              <dmCode modelIdentCode="S1000D" systemDiffCode="G" systemCode="04" subSystemCode="1" subSubSystemCode="0" assyCode="0301" disassyCode="00" disassyCodeVariant="A" infoCode="022" infoCodeVariant="A" itemLocationCode="D"/>
              <language languageIsoCode="en" countryIsoCode="US"/>
              <issueInfo issueNumber="001" inWork="00"/>
            </dmIdent>
            <dmAddressItems>
              <issueDate year="2019" month="06" day="28"/>
              <dmTitle>
                <techName>S1000D</techName>
                <infoName>Business rules exchange</infoName>
              </dmTitle>
            </dmAddressItems>
          </dmAddress>
          <dmStatus issueType="new">
            <security securityClassification="01"/>
            <dataRestrictions>
              <restrictionInstructions>
                <dataDistribution>To be made available to all S1000D users.</dataDistribution>
                <exportControl>
                  <exportRegistrationStmt>
                    <simplePara>Export of this data module to all countries that are the residence of
                      organizations that are users of S1000D is permitted. Storage of this data module
                      is to be at the discretion of the organization.</simplePara>
                  </exportRegistrationStmt>
                </exportControl>
                <dataHandling>There are no specific handling instructions for this data
                  module.</dataHandling>
                <dataDestruction>Users may destroy this data module in accordance with their own local
                  procedures.</dataDestruction>
                <dataDisclosure>There are no dissemination limitations that apply to this data
                  module.</dataDisclosure>
              </restrictionInstructions>
              <restrictionInfo>
                <copyright>
                  <copyrightPara>
                    <emphasis>Copyright (C) 2019</emphasis> by each of the following organizations:
                    <randomList>   
                      <listItem> 
                        <para>AeroSpace and Defence Industries Associations of Europe - ASD.</para> 
                      </listItem> 
                      <listItem> 
                        <para>Ministries of Defence of the member countries of ASD.</para> 
                      </listItem> 
                    </randomList> 
                  </copyrightPara> 
                  <copyrightPara> 
                    <emphasis>Limitations of liability:</emphasis> 
                  </copyrightPara> 
                  <copyrightPara> 
                    <randomList> 
                      <listItem> 
                        <para>This material is provided "As is" and neither ASD nor any person who has contributed to the creation, revision or maintenance of the material makes any representations or warranties, express or implied, including but not limited to, warranties of merchantability or fitness for any particular purpose.</para> 
                      </listItem> 
                      <listItem> 
                        <para>Neither ASD nor any person who has contributed to the creation, revision or maintenance of this material shall be liable for any direct, indirect, special or consequential damages or any other liability arising from any use of this material.</para> 
                      </listItem> 
                      <listItem> 
                        <para>Revisions to this document may occur after its issuance. The user is responsible for determining if revisions to the material contained in this document have occurred and are applicable.</para> 
                      </listItem> 
                    </randomList> 
                  </copyrightPara> 
                </copyright> 
                <policyStatement>S1000D-SC-2016-017-002-00 Steering Committee TOR</policyStatement> 
                <dataConds>There are no known conditions that would change the data restrictions for, or security classification of, this data module.</dataConds> 
              </restrictionInfo> 
            </dataRestrictions> 
            <responsiblePartnerCompany enterpriseCode="B6865"> 
              <enterpriseName>AeroSpace and Defence Industries Association of Europe - ASD</enterpriseName> 
            </responsiblePartnerCompany> 
            <originator enterpriseCode="B6865"> 
              <enterpriseName>AeroSpace and Defence Industries Association of Europe - ASD</enterpriseName> 
            </originator>
            <applic>
              <displayText>
                <simplePara>All</simplePara>
              </displayText>
            </applic>
            <brexDmRef>
              <dmRef>
                <dmRefIdent>
                  <dmCode modelIdentCode="S1000D" systemDiffCode="G" systemCode="04" subSystemCode="1" subSubSystemCode="0" assyCode="0301" disassyCode="00" disassyCodeVariant="A" infoCode="022" infoCodeVariant="A" itemLocationCode="D"/>
                  <issueInfo issueNumber="001" inWork="00"/>
                </dmRefIdent>
              </dmRef>
            </brexDmRef>
            <qualityAssurance>
              <firstVerification verificationType="tabtop"/>
            </qualityAssurance>
            <reasonForUpdate id="EPWG3" updateReasonType="urt01" updateHighlight="0">
              <simplePara>EPWG 86.09: Editorial changes.</simplePara>
              <simplePara>Unnecessary markup has been removed.</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="CPF2019-007NN" updateReasonType="urt02" updateHighlight="1">
              <simplePara>CPF_2019-007NN: FM and Regulatory Information.</simplePara>
              <simplePara>New rules introduced to disallow use of attribute controlAuthorityRefs in front matter. Related existing rules revised.</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="CPF2019-001EPWG" updateReasonType="urt02" updateHighlight="1">
              <simplePara>CPF_2019-001EPWG: Identification of Regulatory Information Addendum.</simplePara>
              <simplePara>New rules introduced to disallow use of attribute controlAuthorityRefs in Comment and ICN metadata objects.</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="EPWG2" updateReasonType="urt01" updateHighlight="0">
              <simplePara>EPWG 86.07: Correct case IC 169.</simplePara>
              <simplePara>In IC 169, description changed from "... Balance" to "... balance".</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="CPF2019-004EPWG" updateReasonType="urt02" updateHighlight="1">
              <simplePara>CPF_2019-004EPWG: Add brLevelledPara as referable para.</simplePara>
              <simplePara>brLevelledPara added to rule BREX-S1-00051 to allow references to the element.</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="CPF2018-002US" updateReasonType="urt02" updateHighlight="1">
              <simplePara>CPF_2018-002US: New information code for Flush.</simplePara>
              <simplePara>Information code 235 (Flush) is added.</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="CPF2017-014AA" updateReasonType="urt02" updateHighlight="1">
              <simplePara>CPF_2017-014AA: Add new Info Codes.</simplePara>
              <simplePara>Information codes 025 (Export control policy) and 026 (Export control policy) are added.</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="CPF2017-013AA" updateReasonType="urt02" updateHighlight="1">
              <simplePara>CPF_2017-013AA: Conflicting Usage Instructions for @allowedObjectFlag Attribute.</simplePara>
              <simplePara>Rule BREX-S1-00149 is no longer needed since attribute allowedObjectFlag is now mandated by the schema, thus deleted.</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="CPF2015-016AA" updateReasonType="urt02" updateHighlight="1">
              <simplePara>CPF_2015-016AA: Identification of Regulatory Information.</simplePara>
              <simplePara>Attributes @authorityName and @authorityDocument replaced by more comprehensive structure referred by new project configurable attribute @controlAuthorityRefs.</simplePara>
              <simplePara>Pre-defined values added for the new attribute (Rule BREX-S1-00270).</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="CPF2013-072US" updateReasonType="urt02" updateHighlight="1">
              <simplePara>CPF_2013-072US: New information names for 022 and 024.</simplePara>
              <simplePara>Information names for codes 022 and 024 are changed (Rule BREX-S1-00180). Information name of this data module is changed accordingly.</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="CPF2013-069EPWG" updateReasonType="urt02" updateHighlight="1">
              <simplePara>CPF_2013-069EPWG: Default BREX @sparePartClassCode vs XML Schema inconsistency.</simplePara>
              <simplePara>Rule BREX-S1-00090 for @sparePartClassCode removed since value set now controlled by schema.</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="CPF2013-066EPWG" updateReasonType="urt02" updateHighlight="1">
              <simplePara>CPF_2013-066EPWG: Issue Number Greater Than 999.</simplePara>
              <simplePara>Rules are added to control the number of digits in issue number depending of the size of the number.</simplePara>
            </reasonForUpdate>
            <reasonForUpdate id="CPF2011-009DE" updateReasonType="urt02" updateHighlight="1">
              <simplePara>CPF_2011-009DE: Information name variant.</simplePara>
              <simplePara>Rule added to prohibit use of element infoNameVariant when element infoName is not given.</simplePara>
            </reasonForUpdate>
          </dmStatus>
        </identAndStatusSection>
    EOD;
    $dmodule .= self::content();
    $dmodule .= "</dmodule>";
    return $dmodule;
  }

  public static function content()
  {
    return <<<EOD
    <content>
          <brex>
            <commonInfo>
              <para>This default BREX data module is provided with Issue 5.0 of S1000D. It contains in total 265 rules consisting of: <randomList>
                  <listItem>
                    <para>firm rules expressed in the specification text, not supported by the schema structures, however, such that they can be represented by an XPath expression and such that a CSDB object can be automatically be verified against these XPath rules</para>
                  </listItem>
                  <listItem>
                    <para>narrative rules expressed in the specification text, not supported by the schema structures, and not possible to formalize in an XPath expression, hence, not useful in an automatic rules checking procedure</para>
                  </listItem>
                </randomList>
              </para>
              <para>The highest rule number is 00275.</para>
              <para>CPF 2009-039S1 introduced an attribute valueTailoring (on element objectValue) specifying to which degree a certain BREX data module may adjust the use/interpretation of a given value, e.g. a configurable attribute. The values are specified as follows (refer to Chap 4.10.2.2): <randomList>
                  <listItem>
                    <para>
                      <emphasis>restrictable</emphasis> - a BREX at a lower level may impose further restrictions to the given value by changing the textual interpretation to reflect that restriction</para>
                  </listItem>
                  <listItem>
                    <para>
                      <emphasis>lexical</emphasis> - a BREX at a lower level may adjust the text string constituting the interpretation, however, only in respects that do not in any way change the conceptual and semantic meaning of the interpretation</para>
                  </listItem>
                  <listItem>
                    <para>
                      <emphasis>closed</emphasis> (default value) - a BREX at a lower level must not in any way restrict or alter the interpretation of a value declared at a higher level.</para>
                  </listItem>
                </randomList>
              </para>
            </commonInfo>
            <contextRules rulesContext="http://www.s1000d.org/S1000D_5-0/xml_schema_flat/dml.xsd">
              <!-- DML specific rules -->
              <structureObjectRuleGroup>
                <!-- 4.5 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00001" />
                  <objectPath allowedObjectFlag="0">//dml[(descendant-or-self::dmlIdent[child::dmlCode[attribute::dmlType!="s"]]) and ((descendant-or-self::dmlEntry[child::dmlRef]) or (descendant-or-self::dmlEntry[child::commentRef]))]</objectPath>
                  <objectUse>A DMRL must not contain comments or other data management lists (Chap 4.5, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00002" />
                  <objectPath allowedObjectFlag="2">//dmlEntry</objectPath>
                  <objectUse>The DMRL must contain the titles and details of the responsible partner company for each of the data modules and publication modules - with some exceptions (Chap 4.5, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00003" />
                  <objectPath allowedObjectFlag="2">//dmlEntry</objectPath>
                  <objectUse>The DMRL does not contain the issue information of its contained objects, unless under certain circumstances (Chap 4.5, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00004" />
                  <objectPath allowedObjectFlag="0">//dml[descendant-or-self::dmlAddress[descendant-or-self::dmlIdent[child::dmlCode[attribute::dmlType="s"]]] and descendant-or-self::dmlEntry[(descendant-or-self::dmRefIdent[not(child::issueInfo)] or descendant-or-self::pmRefIdent[not(child::issueInfo)] or descendant-or-self::dmlRefIdent[not(child::issueInfo)])]]</objectPath>
                  <objectUse>The CSL must contain the issue numbers of the CSDB objects it contains (Chap 4.5, Para 3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00005" />
                  <objectPath allowedObjectFlag="0">//dml[descendant-or-self::dmlStatus[attribute::issueType = "new"] and descendant-or-self::dmlAddress[descendant-or-self::issueInfo[attribute::issueNumber != "000" and attribute::issueNumber != "001"]]]</objectPath>
                  <objectUse >A new data module list must not have an issue number exceeding 001 (Chap 4.5, Para 4.1.1.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00006" />
                  <objectPath allowedObjectFlag="0">//dml[descendant-or-self::dmlAddress[descendant-or-self::dmlCode[attribute::dmlType="s"]] and descendant-or-self::dmlStatus[child::dmlRef[child::dmlRefIdent[child::dmlCode[attribute::dmlType != "s"]]]]]</objectPath>
                  <objectUse>References in a CSDB status list must only refer to other CSDB status list.(Chap 4.5, Para 4.1.2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00007" />
                  <objectPath allowedObjectFlag="0">//dml[descendant-or-self::dmlIdent[child::dmlCode[attribute::dmlType="s"]] and descendant-or-self::dmlEntry[child::answer]]</objectPath>
                  <objectUse>The element answer must not be used in a CSL (Chap 4.5, Para 4.2.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00008" />
                  <objectPath allowedObjectFlag="0">//dml[descendant-or-self::dmlIdent[child::dmlCode[attribute::dmlType!="s"]] and descendant-or-self::dmlEntry[child::answer and (child::pmRef or child::infoEntityRef or child::dmlRef or child::commentRef)]]</objectPath>
                  <objectUse>When used (in a DMRL), the element answer must only be used for data module entries (Chap 4.5, Para 4.2.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00009" />
                  <objectPath allowedObjectFlag="2">//dmlCode/@seqNumber</objectPath>
                  <objectUse>The sequential number in the DML identity should start at 00001 (Chap 4.5, Para 5.1 and Para 5.2).</objectUse>
                </structureObjectRule>
              </structureObjectRuleGroup>
            </contextRules>
            <contextRules rulesContext="http://www.s1000d.org/S1000D_5-0/xml_schema_flat/comment.xsd">
              <!-- COMMENT specific rules -->
              <structureObjectRuleGroup>
                <!-- 4.6 -->
                <structureObjectRule reasonForUpdateRefIds="CPF2019-001EPWG" changeType="add" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00272" />
                  <objectPath allowedObjectFlag="0">//comment[descendant::*[attribute::controlAuthorityRefs]]</objectPath>
                  <objectUse>Control authority references must not be used in comments. (Chap 4.6, Para 2).</objectUse>
                </structureObjectRule>
                <!-- 4.6.1 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00010" />
                  <objectPath allowedObjectFlag="2">//commentCode/@seqNumber</objectPath>
                  <objectUse>The sequential number in the Comment identity should start at 00001 (Chap 4.6.1, Para 2.1.1.1.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00011" />
                  <objectPath allowedObjectFlag="2">//commentRefs</objectPath>
                  <objectUse>For a comment related to a data module instance identified by the use of the data module code extension, the reference must include the element identExtension contained in element dmRef within this block of references. (Chap 4.6.1, Para 2.2.3).</objectUse>
                </structureObjectRule>
              </structureObjectRuleGroup>
            </contextRules>
            <contextRules rulesContext="http://www.s1000d.org/S1000D_5-0/xml_schema_flat/ddn.xsd">
              <!-- DDN specific rules -->
              <structureObjectRuleGroup>
                <!-- 4.4 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00012" />
                  <objectPath allowedObjectFlag="0">//ddn[descendant-or-self::entityControlNumber[contains((.),"ICN-")]]</objectPath>
                  <objectUse >The entity control number given in a DDN must not include the prefix ICN- (Chap 4.4, Para 2).</objectUse>
                </structureObjectRule>
              </structureObjectRuleGroup>
            </contextRules>
            <contextRules rulesContext="http://www.s1000d.org/S1000D_5-0/xml_schema_flat/icnmetadata.xsd">
              <!-- ICN metadata specific rules -->
              <structureObjectRuleGroup>
                <!-- 3.9.2.7 -->
                <structureObjectRule reasonForUpdateRefIds="CPF2019-001EPWG" changeType="add" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00273" />
                  <objectPath allowedObjectFlag="0">//icnMetadataFile[descendant::*[attribute::controlAuthorityRefs]]</objectPath>
                  <objectUse>Control authority references must not be used in ICN metadata. (Chap 3.9.2.7, Para 2).</objectUse>
                </structureObjectRule>
              </structureObjectRuleGroup>
            </contextRules>
            <contextRules rulesContext="http://www.s1000d.org/S1000D_5-0/xml_schema_flat/pm.xsd">
              <!-- PM specific rules -->
              <structureObjectRuleGroup>
                <!-- 3.9.5.2.1.1 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00013" />
                  <objectPath allowedObjectFlag="0">//*[attribute::changeMark = "1" and ancestor::pm[child::identAndStatusSection[child::pmStatus[attribute::issueType != "changed" and attribute::issueType != "rinstate-changed"]]]]</objectPath>
                  <objectUse>No change markers must appear in a publication module if the issue type is not changed (Chap 3.9.5.2.1.1, Para 2.1.1).</objectUse>
                </structureObjectRule>
              </structureObjectRuleGroup>
            </contextRules>
            <contextRules rulesContext="http://www.s1000d.org/S1000D_5-0/xml_schema_flat/update.xsd">
              <!-- UPDATE specific rules -->
              <structureObjectRuleGroup>
                <!-- 4.13.2.1 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00022" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="0A1" and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::partSpec or descendant::circuitBreakerSpec or descendant::functionalItemSpec or descendant::accessPointSpec or descendant::enterpriseSpec or descendant::toolSpec or descendant::supplySpec or descendant::controlIndicatorSpec or descendant::supplyRqmtSpec or descendant::applicSpec or descendant::warningSpec or descendant::cautionSpec or descendant::zoneIdent or descendant::functionalItemIdent or descendant::circuitBreakerIdent or descendant::accessPointIdent or descendant::toolIdent or descendant::enterpriseIdent or descendant::supplyIdent or descendant::supplyRqmtIdent or descendant::partIdent or descendant::controlIndicatorIdent or descendant::applicSpecIdent or descendant::warningIdent or descendant::cautionIdent]]]</objectPath>
                  <objectUse>Only functionalPhysicalAreaSpec, functionalPhysicalAreaIdent, applicIdent, applicRefIdent, applic, applicRef, figure, figureIdent, multimedia, multimediaIdent elements can be used in the Data update file representing the functional physical area CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00024" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="0A2" and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::partSpec or descendant::circuitBreakerSpec or descendant::functionalItemSpec or descendant::accessPointSpec or descendant::enterpriseSpec or descendant::toolSpec or descendant::supplySpec or descendant::functionalPhysicalAreaSpec or descendant::controlIndicatorSpec or descendant::supplyRqmtSpec or descendant::multimedia or descendant::figure or descendant::warningSpec or descendant::cautionSpec or descendant::zoneIdent or descendant::functionalItemIdent or descendant::circuitBreakerIdent or descendant::accessPointIdent or descendant::toolIdent or descendant::enterpriseIdent or descendant::supplyIdent or descendant::supplyRqmtIdent or descendant::functionalPhysicalAreaIdent or descendant::controlIndicatorIdent or descendant::partIdent or descendant::multimediaIdent  or descendant::figureIdent or descendant::warningIdent or descendant::cautionIdent or descendant::applicRef or descendant::applicRefIdent]]]</objectPath>
                  <objectUse>Only applicSpec, applicSpecIdent, applic, applicIdent elements can be used in the Data update file representing the applicabilities CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00016" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="00E" and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::partSpec or descendant::circuitBreakerSpec or descendant::accessPointSpec or descendant::toolSpec or descendant::enterpriseSpec or descendant::supplySpec or descendant::supplyRqmtSpec or descendant::functionalPhysicalAreaSpec or descendant::controlIndicatorSpec or descendant::applicSpec or descendant::warningSpec or descendant::cautionSpec or descendant::zoneIdent or descendant::partIdent or descendant::circuitBreakerIdent or descendant::accessPointIdent or descendant::toolIdent or descendant::enterpriseIdent or descendant::supplyIdent or descendant::supplyRqmtIdent or descendant::functionalPhysicalAreaIdent or descendant::controlIndicatorIdent or descendant::applicSpecIdent or descendant::warningIdent or descendant::cautionIdent]]]</objectPath>
                  <objectUse>Only functionalItemSpec, functionalItemIdent, figure, figureIdent, multimedia, multimediaIdent, applicIdent, applicRefIdent, applic, applicRef elements must be used in the Data update file representing the FIN CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00017" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="00F" and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::partSpec or descendant::functionalItemSpec or descendant::accessPointSpec or descendant::toolSpec or descendant::enterpriseSpec or descendant::supplySpec or descendant::supplyRqmtSpec or descendant::functionalPhysicalAreaSpec or descendant::controlIndicatorSpec or descendant::applicSpec or descendant::warningSpec or descendant::cautionSpec or descendant::zoneIdent or descendant::partIdent or descendant::functionalItemIdent or descendant::accessPointIdent or descendant::toolIdent or descendant::enterpriseIdent or descendant::supplyIdent or descendant::supplyRqmtIdent or descendant::functionalPhysicalAreaIdent or descendant::controlIndicatorIdent or descendant::applicSpecIdent or descendant::warningIdent or descendant::cautionIdent]]]</objectPath>
                  <objectUse>Only circuitBreakerSpec, circuitBreakerIdent, applicIdent, applicRefIdent, applic, applicRef, figure, figureIdent, multimedia, multimediaIdent elements can be used in the Data update file representing the CB CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00014" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="00G" and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::functionalItemSpec or descendant::circuitBreakerSpec or descendant::accessPointSpec or descendant::toolSpec or descendant::enterpriseSpec or descendant::supplySpec or descendant::supplyRqmtSpec or descendant::functionalPhysicalAreaSpec or descendant::controlIndicatorSpec or descendant::applicSpec or descendant::warningSpec or descendant::cautionSpec or descendant::zoneIdent or descendant::functionalItemIdent or descendant::circuitBreakerIdent or descendant::accessPointIdent or descendant::toolIdent or descendant::enterpriseIdent or descendant::supplyIdent or descendant::supplyRqmtIdent or descendant::functionalPhysicalAreaIdent or descendant::controlIndicatorIdent or descendant::applicSpecIdent or descendant::warningIdent or descendant::cautionIdent]]]</objectPath>
                  <objectUse>Only partSpec, partIdent, figure, figureIdent, multimedia, multimediaIdent, applic, applicIdent, applicRef and applicRefIdent elements can be used in the Data update file representing the part CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00015" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="00H" and ancestor::dataUpdateFile[descendant::update[descendant::partSpec or descendant::functionalItemSpec or descendant::circuitBreakerSpec or descendant::accessPointSpec or descendant::toolSpec or descendant::enterpriseSpec or descendant::supplySpec or descendant::supplyRqmtSpec or descendant::functionalPhysicalAreaSpec or descendant::controlIndicatorSpec or descendant::applicSpec or descendant::warningSpec or descendant::cautionSpec or descendant::partIdent or descendant::functionalItemIdent or descendant::circuitBreakerIdent or descendant::accessPointIdent or descendant::toolIdent or descendant::enterpriseIdent or descendant::supplyIdent or descendant::supplyRqmtIdent or descendant::functionalPhysicalAreaIdent or descendant::controlIndicatorIdent or descendant::applicSpecIdent or descendant::warningIdent or descendant::cautionIdent]]]</objectPath>
                  <objectUse>Only zoneSpec, zoneIdent, figure, figureIdent, multimedia, multimediaIdent, applicIdent, applicRefIdent, applic, applicRef elements can be used in the Data update file representing the zone CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00018" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="00J" and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::partSpec or descendant::circuitBreakerSpec or descendant::functionalItemSpec or descendant::toolSpec or descendant::enterpriseSpec or descendant::supplySpec or descendant::supplyRqmtSpec or descendant::functionalPhysicalAreaSpec or descendant::controlIndicatorSpec or descendant::applicSpec or descendant::warningSpec or descendant::cautionSpec or descendant::zoneIdent or descendant::partIdent or descendant::functionalItemIdent or descendant::circuitBreakerIdent or descendant::toolIdent or descendant::enterpriseIdent or descendant::supplyIdent or descendant::supplyRqmtIdent or descendant::functionalPhysicalAreaIdent or descendant::controlIndicatorIdent or descendant::applicSpecIdent or descendant::warningIdent or descendant::cautionIdent]]]</objectPath>
                  <objectUse>Only accessPointSpec, accessPointIdent, figure, figureIdent, multimedia, multimediaIdent, applic, applicRef, applicIdent, applicRefIdent elements can be used in the Data update file representing the access point CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00025" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="00K" and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::partSpec or descendant::circuitBreakerSpec or descendant::functionalItemSpec or descendant::accessPointSpec or descendant::applicSpec or descendant::toolSpec or descendant::supplySpec or descendant::functionalPhysicalAreaSpec or descendant::controlIndicatorSpec or descendant::supplyRqmtSpec or descendant::warningSpec or descendant::cautionSpec or descendant::zoneIdent or descendant::functionalItemIdent or descendant::circuitBreakerIdent or descendant::accessPointIdent or descendant::toolIdent or descendant::partIdent or descendant::supplyIdent or descendant::supplyRqmtIdent or descendant::functionalPhysicalAreaIdent or descendant::controlIndicatorIdent or descendant::applicSpecIdent or descendant::warningIdent or descendant::cautionIdent or descendant::applicRef or descendant::applicRefIdent or descendant::applic or descendant::applicIdent]]]</objectPath>
                  <objectUse>Only enterpriseSpec, enterpriseIdent, figure, figureIdent, multimedia, multimediaIdent elements can be used in the Data update file representing the enterprise CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00020" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="00L" and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::partSpec or descendant::circuitBreakerSpec or descendant::functionalItemSpec or descendant::accessPointSpec or descendant::enterpriseSpec or descendant::toolSpec or descendant::supplyRqmtSpec or descendant::functionalPhysicalAreaSpec or descendant::controlIndicatorSpec or descendant::applicSpec or descendant::warningSpec or descendant::cautionSpec or descendant::zoneIdent or descendant::functionalItemIdent or descendant::circuitBreakerIdent or descendant::accessPointIdent or descendant::toolIdent or descendant::enterpriseIdent or descendant::partIdent or descendant::supplyRqmtIdent or descendant::functionalPhysicalAreaIdent or descendant::controlIndicatorIdent or descendant::applicSpecIdent or descendant::warningIdent or descendant::cautionIdent]]]</objectPath>
                  <objectUse>Only supplySpec, supplyIdent, figure, figureIdent, multimedia, multimediaIdent, applic, applicRef, applicIdent, applicRefIdent elements can be used in the Data update file representing the supply CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00021" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="00M" and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::partSpec or descendant::circuitBreakerSpec or descendant::functionalItemSpec or descendant::accessPointSpec or descendant::enterpriseSpec or descendant::toolSpec or descendant::supplySpec or descendant::functionalPhysicalAreaSpec or descendant::controlIndicatorSpec or descendant::applicSpec or descendant::warningSpec or descendant::cautionSpec or descendant::zoneIdent or descendant::functionalItemIdent or descendant::circuitBreakerIdent or descendant::accessPointIdent or descendant::toolIdent or descendant::enterpriseIdent or descendant::supplyIdent or descendant::partIdent or descendant::functionalPhysicalAreaIdent or descendant::controlIndicatorIdent or descendant::applicSpecIdent or descendant::warningIdent or descendant::cautionIdent]]]</objectPath>
                  <objectUse>Only supplyRqmtSpec, supplyRqmtIdent, applicIdent, applicRefIdent, applic, applicRef, figure, figureIdent, multimedia, multimediaIdent elements can be used in the Data update file representing the supply requirements CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00019" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="00N" and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::partSpec or descendant::circuitBreakerSpec or descendant::functionalItemSpec or descendant::accessPointSpec or descendant::enterpriseSpec or descendant::supplySpec or descendant::supplyRqmtSpec or descendant::functionalPhysicalAreaSpec or descendant::controlIndicatorSpec or descendant::applicSpec or descendant::warningSpec or descendant::cautionSpec or descendant::zoneIdent or descendant::partIdent or descendant::functionalItemIdent or descendant::circuitBreakerIdent or descendant::accessPointIdent or descendant::enterpriseIdent or descendant::supplyIdent or descendant::supplyRqmtIdent or descendant::functionalPhysicalAreaIdent or descendant::controlIndicatorIdent or descendant::applicSpecIdent or descendant::warningIdent or descendant::cautionIdent]]]</objectPath>
                  <objectUse>Only toolSpec, toolIdent, figure, figureIdent, multimedia, multimediaIdent, applicIdent, applicRefIdent, applic, applicRef elements can be used in the Data update file representing the tool CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00023" />
                  <objectPath allowedObjectFlag="0">//updateCode[attribute::infoCode="00X" and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::partSpec or descendant::circuitBreakerSpec or descendant::functionalItemSpec or descendant::accessPointSpec or descendant::enterpriseSpec or descendant::toolSpec or descendant::supplySpec or descendant::functionalPhysicalAreaSpec or descendant::supplyRqmtSpec or descendant::applicSpec or descendant::warningSpec or descendant::cautionSpec or descendant::zoneIdent or descendant::functionalItemIdent or descendant::circuitBreakerIdent or descendant::accessPointIdent or descendant::toolIdent or descendant::enterpriseIdent or descendant::supplyIdent or descendant::supplyRqmtIdent or descendant::functionalPhysicalAreaIdent or descendant::partIdent or descendant::applicSpecIdent or descendant::warningIdent or descendant::cautionIdent]]]</objectPath>
                  <objectUse>Only controlIndicatorSpec, controlIndicatorIdent, figure, figureIdent, multimedia, multimediaIdent, applicIdent, applicRefIdent, applic, applicRef elements can be used in the Data update file representing the control indicators CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00026" />
                  <objectPath allowedObjectFlag="0">//updateCode[(attribute::infoCode="012" or attribute::infoCode="0A4" or attribute::infoCode="0A5") and ancestor::dataUpdateFile[descendant::update[descendant::zoneSpec or descendant::partSpec or descendant::circuitBreakerSpec or descendant::functionalItemSpec or descendant::accessPointSpec or descendant::applicSpec or descendant::toolSpec or descendant::enterpriseSpec or descendant::supplySpec or descendant::functionalPhysicalAreaSpec or descendant::controlIndicatorSpec or descendant::supplyRqmtSpec or descendant::zoneIdent or descendant::functionalItemIdent or descendant::circuitBreakerIdent or descendant::accessPointIdent or descendant::toolIdent or descendant::partIdent or descendant::supplyIdent or descendant::supplyRqmtIdent or descendant::functionalPhysicalAreaIdent or descendant::controlIndicatorIdent or descendant::applicSpecIdent or descendant::enterpriseIdent]]]</objectPath>
                  <objectUse>Only warningSpec, warningIdent, cautionSpec, cautionIdent, applicIdent, applicRefIdent, applic, applicRef, figure, figureIdent, multimedia, multimediaIdent elements can be used in the Data update file representing the warning and caution CIR (Chap 4.13.2.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
              </structureObjectRuleGroup>
            </contextRules>
            <contextRules>
              <!-- GENERAL rules -->
              <structureObjectRuleGroup>
                <!-- 1.4.2 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00027" />
                  <objectPath allowedObjectFlag="0">/*[not(self::dmodule or self::pm or self::ddn or self::dml or self::comment or self::dataUpdateFile or self::scormContentPackage or self::icnMetadataFile)]</objectPath>
                  <objectUse >The root element of an interchanged xml CSDB object must be one of dmodule, pm, ddn, dml, comment, dataUpdateFile, scormContentPackage or icnMetadataFile (Chap 1.4.2, Para 2.3.1.3.1).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.1 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00029" />
                  <objectPath allowedObjectFlag="2">//language</objectPath>
                  <objectUse>Population of attribute countryIsoCode and attribute languageIsoCode should be in accordance with ISO 3166 and ISO 639, respectively (Chap 3.9.5.1, Para 2.1.1.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2011-009DE" changeType="add" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00269" />
                  <objectPath allowedObjectFlag="0">//infoNameVariant[not(preceding-sibling::infoName)]</objectPath>
                  <objectUse>infoNameVariant must not be specified when infoName is not given. (Chap 3.9.5.1, Para 2.1.2.2.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00030" />
                  <objectPath allowedObjectFlag="0">//identAndStatusSection[dmStatus/@issueType!="new"]//dmAddress//issueInfo[@issueNumber="000" or (@issueNumber="001" and @inWork="00")]</objectPath>
                  <objectUse>Data modules up to and including the initial issue of the approved release must have the attribute issueNumber set to the value 000 for inwork versions or value 001 for the initial issue and have the attribute issueType set to the value /new/. (Chap 3.9.5.1, Para 2.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00031" />
                  <objectPath allowedObjectFlag="2">//@issueType</objectPath>
                  <objectUse>Deletion of data modules is treated as a special case of update. The data module itself is not physically deleted from the CSDB but marked as deleted by setting the attribute issueType to the value /deleted/. (Chap 3.9.5.1, Para 2.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00032" />
                  <objectPath allowedObjectFlag="0">//dmodule[(descendant-or-self::dmAddress[descendant-or-self::issueInfo[attribute::inwork="00"]]) and (child::content[descendant-or-self::*[attribute::changeMark or attribute::changeType]] and not(descendant-or-self::dmStatus[attribute::issueType="changed" or attribute::issueType="rinstate-changed"]))]</objectPath>
                  <objectUse>Published data modules that have been changed and have the changes indicated within the data module using change elements and attributes, must have the attribute issueType set to the value /changed/ or, if the data module is reinstated, set to /rinstate-changed/. (Chap 3.9.5.1, Para 2.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00033" />
                  <objectPath allowedObjectFlag="0">//dmodule[child::content[descendant-or-self::*[attribute::changeMark or attribute::changeType]] and descendant-or-self::dmStatus[attribute::issueType="revised" or attribute::issueType="rinstate-revised"]]</objectPath>
                  <objectUse>Data modules that have been totally revised and that contain no change elements or attributes must have the attribute issueType set to the value /revised/ or, if the data module is reinstated, set to /rinstate-revised/. (Chap 3.9.5.1, Para 2.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00034" />
                  <objectPath allowedObjectFlag="0">//dmodule[not(descendant-or-self::identAndStatusSection[descendant-or-self::reasonForUpdate]) and not(child::content[descendant-or-self::*[attribute::changeType or attribute::changeMark]]) and (child::identAndStatusSection[descendant-or-self::*[attribute::changeType or attribute::changeMark]]) and descendant-or-self::dmStatus[not(attribute::issueType="status" or attribute::issueType="rinstate-status")]]</objectPath>
                  <objectUse>Data modules that have had their identification and status information updated must have the attribute issueType set to the value /status/ or, if the data module is reinstated, set to /rinstate-status/. (Chap 3.9.5.1, Para 2.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00035" />
                  <objectPath allowedObjectFlag="0">//responsiblePartnerCompany[(not(attribute::enterpriseCode) or attribute::enterpriseCode = "") and (not(child::enterpriseName) or child::enterpriseName = "")]</objectPath>
                  <objectUse>Company or organization must be indicated by at least one of either the name of the company and/or the companys CAGE code, .... However, if a responsible partner company has an enterprise code, then that code must be used (Chap 3.9.5.1, Para 2.2.7).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.1.1 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00036" />
                  <objectPath allowedObjectFlag="0">//dmodule[not(descendant-or-self::dmAddress[descendant-or-self::dmCode[attribute::modelIdentCode = "S1000D"]]) and (descendant-or-self::changeInline or descendant-or-self::*[attribute::changeMark]) and child::identAndStatusSection[child::dmStatus[attribute::issueType != "changed" and attribute::issueType != "rinstate-changed"]]]</objectPath>
                  <objectUse >No change markers must appear if the issue type is not changed (Chap 3.9.5.2.1.1, Para 2.1.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00038" />
                  <objectPath allowedObjectFlag="0">//dmodule[descendant-or-self::dmStatus[attribute::issueType != "changed"] and descendant-or-self::dmAddress[descendant-or-self::issueInfo[attribute::issueNumber != "000" and attribute::issueNumber != "001"]] and not(descendant-or-self::reasonForUpdate)]</objectPath>
                  <objectUse >Data modules that are not of issue type changed must also have at least one reason for update element if the issue number is greater than 001 (Chap 3.9.5.2.1.1, Para 2.1.1 and Para 2.1.2).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00039" />
                  <objectPath allowedObjectFlag="2">//reasonForUpdate</objectPath>
                  <objectUse>If attribute updateHighlight is set to the value 1, this indicates that the reason for update must appear in the highlights data module. If the attribute is not used or its value is 0, it means that the reason for update must not appear in the highlights data module (Chap 3.9.5.2.1.1, Para 2.2).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00040" />
                  <objectPath allowedObjectFlag="2">//*/changeInline</objectPath>
                  <objectUse>The element /changeInline/ must not be used to indicate that a complete element has been inserted (or modified) (Chap 3.9.5.2.1.1, Para 2.4.2).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00041" />
                  <objectPath allowedObjectFlag="2">//internalRef</objectPath>
                  <objectUse>There must be no cross-references to deleted information in the data module (Chap 3.9.5.2.1.1, Para 2.4.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00042" />
                  <objectPath allowedObjectFlag="2">//dmRef</objectPath>
                  <objectUse>There must be no references to deleted information in other data modules (Chap 3.9.5.2.1.1, Para 2.4.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00043" />
                  <objectPath allowedObjectFlag="0">//graphic[attribute::changeMark="1" and parent::figure[count(child::graphic) > 1] and (ancestor::figure[attribute::changeMark="1"] or ancestor::figureAlts[attribute::changeMark="1"])]</objectPath>
                  <objectUse>If the element /figure/ is change marked, the change attributes on the element /graphic/ of multi-sheet figures must not be used (Chap 3.9.5.2.1.1, Para 2.3).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00044" />
                  <objectPath allowedObjectFlag="2">//@changeMark</objectPath>
                  <objectUse>Editorial changes must not be marked (Chap 3.9.5.2.1.1, Para 2.1.1).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.1.2 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00045" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt01" and not(attribute::internalRefId = //figure/@id or attribute::internalRefId = //figureAlts/@id)]</objectPath>
                  <objectUse>Only when the reference target is a figure can the value of attribute internalRefTargetType be irtt01 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00046" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt02" and not(attribute::internalRefId = //table/@id)]</objectPath>
                  <objectUse>Only when the reference target is a table can the value of attribute internalRefTargetType be irtt02 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00047" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt03" and not(attribute::internalRefId = //multimedia/@id or attribute::internalRefId = //multimediaAlts/@id)]</objectPath>
                  <objectUse>Only when the reference target is multimedia (containing one or more objects) can the value of attribute internalRefTargetType be irtt03 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00048" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt04" and not(attribute::internalRefId = //supplyDescr/@id) and not(attribute::internalRefId = //embeddedSupplyDescr/@id)]</objectPath>
                  <objectUse>Only when the reference target is a supply  can the value of attribute internalRefTargetType be irtt04 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00049" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt05" and not(attribute::internalRefId = //supportEquipDescr/@id) and not (attribute::internalRefId = //embeddedSupportEquipDescr/@id) and not(attribute::internalRefId = //toolRef/@id) and not(attribute::internalRefId = //toolsListCode/@id)]</objectPath>
                  <objectUse>Only when the reference target is support equipment can the value of attribute internalRefTargetType be irtt05 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00050" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt06" and not(attribute::internalRefId = //spareDescr/@id) and not(attribute::internalRefId = //embeddedSpareDescr/@id)]</objectPath>
                  <objectUse>Only when the reference target is a spare can the value of attribute internalRefTargetType be irtt06 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2019-004EPWG" changeType="modify" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00051" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt07" and not(attribute::internalRefId = //levelledPara/@id or attribute::internalRefId = //levelledParaAlts/@id or attribute::internalRefId = //brLevelledPara/@id)]</objectPath>
                  <objectUse>Only when the reference target is a paragraph can the value of attribute internalRefTargetType be irtt07 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00052" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt08" and not(attribute::internalRefId = //proceduralStep/@id or attribute::internalRefId = //proceduralStepAlts/@id or attribute::internalRefId = //isolationStep/@id or attribute::internalRefId = //isolationStepAlts/@id or attribute::internalRefId = //isolationProcedureEnd/@id or attribute::internalRefId = //isolationProcedureEndAlts/@id or attribute::internalRefId = //crewDrillStep/@id or attribute::internalRefId = //checkListStep/@id)]</objectPath>
                  <objectUse>Only when the reference target is a step can the value of attribute internalRefTargetType be irtt08 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00053" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt09" and not(attribute::internalRefId = //graphic/@id)]</objectPath>
                  <objectUse>Only when the reference target is a figure can the value of attribute internalRefTargetType be irtt09 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00054" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt10" and not(attribute::internalRefId = //multimediaObject/@id)]</objectPath>
                  <objectUse>Only when the reference target is a single multimedia object can the value of attribute internalRefTargetType be irtt10 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00055" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt12" and not(attribute::internalRefId = //parameter/@id)]</objectPath>
                  <objectUse>Only when the reference target is a parameter can the value of attribute internalRefTargetType be irtt12 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00056" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt13" and not(attribute::internalRefId = //zoneRef/@id)]</objectPath>
                  <objectUse>Only when the reference target is a zone can the value of attribute internalRefTargetType be irtt13 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00057" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt14" and not(attribute::internalRefId = //workLocation/@id)]</objectPath>
                  <objectUse>Only when the reference target is a work location can the value of attribute internalRefTargetType be irtt14 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00058" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt15" and not(@internalRefId=//sbMaterialSet/@id) and not(@internalRefId=//sbSupportEquipSet/@id) and not(@internalRefId=//sbIndividualSupportEquip/@id) and not(@internalRefId=//sbExternalSupportEquipSet/@id) and not(@internalRefId=//sbSupplySet/@id) and not(@internalRefId=//sbIndividualSupply/@id) and not(@internalRefId=//sbExternalSupplySet/@id) and not(@internalRefId=//sbSpareSet/@id) and not(@internalRefId=//sbIndividualSpare/@id) and not(@internalRefId=//sbExternalSpareSet/@id) and not(@internalRefId=//sbRemovedSpareSet/@id) and not(@internalRefId=//sbIndividualRemovedSpare/@id)]</objectPath>
                  <objectUse>Only when the reference target is a Service Bulletin material set (including individual, external or removed material) can the value of attribute internalRefTargetType be irtt15 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00059" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt16" and not(attribute::internalRefId = //accessPointRef/@id)]</objectPath>
                  <objectUse>Only when the reference target is an access point can the value of attribute internalRefTargetType be irtt16 (Chap 3.9.5.2.1.2, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00060" />
                  <objectPath allowedObjectFlag="0">//internalRef[attribute::internalRefTargetType="irtt11" and not(attribute::internalRefId = //hotspot/@id or normalize-space(attribute::referredFragment) != "")]</objectPath>
                  <objectUse >Only when the reference target is a hotspot can the value of attribute internalRefTargetType be irtt11 (Chap 3.9.5.2.1.2, Para 2.1, and Chap 3.9.5.2.1.8, Para 2.3 and Para 2.4.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00061" />
                  <objectPath allowedObjectFlag="0">//externalPubIssueDate[(attribute::day and not(attribute::month)) or (attribute::month and not(attribute::year))]</objectPath>
                  <objectUse>In a date, if the month is given then a year must also be given and if the day is given then a month is required. (Chap 3.9.5.2.1.2, Para 2.5.2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00062" />
                  <objectPath allowedObjectFlag="0">//warningRef/dmRef/dmRefIdent/dmCode[(attribute::infoCode!="012" and attribute::infoCode!="0A4")] </objectPath>
                  <objectUse>The dmRef element in a warningRef element must point only at a warning repository data module (Chap 3.9.5.2.1.2, Para 2.7).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00063" />
                  <objectPath allowedObjectFlag="0">//cautionRef/dmRef/dmRefIdent/dmCode[(attribute::infoCode!="012" and attribute::infoCode!="0A5")] </objectPath>
                  <objectUse >The dmRef in a cautionRef element must point only at a caution repository data module (Chap 3.9.5.2.1.2, Para 2.7).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.1.3 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00064" />
                  <objectPath allowedObjectFlag="0">//sequentialList/listItem/para/sequentialList/listItem/para[child::sequentialList]</objectPath>
                  <objectUse>Sequential (ordered) lists are limited to a maximum of two levels (Chap 3.9.5.2.1.3, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00065" />
                  <objectPath allowedObjectFlag="2">//proceduralStep[descendant-or-self::sequentialList]</objectPath>
                  <objectUse>Sequential lists must not be used to provide procedural step information. (Chap 3.9.5.2.1.3, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00066" />
                  <objectPath allowedObjectFlag="2">//sequentialList</objectPath>
                  <objectUse>Only one sequential (ordered) list must be placed under a numbered title or paragraph (subheading) (Chap 3.9.5.2.1.3, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00067" />
                  <objectPath allowedObjectFlag="0">//action/randomList/listItem/para[child::sequentialList or child::randomList or child::definitionList]</objectPath>
                  <objectUse> The use of random lists within a fault isolation action is strictly limited to one level. (Chap 3.9.5.2.1.3, Para 2.2).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.1.5 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00068" />
                  <objectPath allowedObjectFlag="2">//table/title</objectPath>
                  <objectUse>In procedural, process, crew and fault data modules, titles must be included for /figure/ and /table/ for formal tables. (Chap 3.9.5.2.1.5, Para 2).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.1.8 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00069" />
                  <objectPath allowedObjectFlag="0">//internalRef[not(attribute::internalRefId) and (not(attribute::referredFragment) or normalize-space(attribute::referredFragment) = "")]</objectPath>
                  <objectUse>An internal reference must point at a target, either by an internalRefId attribute or a referredFragment attribute (Chap 3.9.5.2.1.8, Para 2.4.1).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.1.9 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00070" />
                  <objectPath allowedObjectFlag="0">//taskDuration[attribute::unitOfMeasure != "h" and attribute::unitOfMeasure != "d"]</objectPath>
                  <objectUse>Task duration time must be given as one of the two characters h or d  (for manhour and manday) (Chap 3.9.5.2.1.9, Para 2.1.3).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00072" />
                  <objectPath allowedObjectFlag="0">//estimatedTime[attribute::unitOfMeasure != "h"]</objectPath>
                  <objectUse>Estimated time must be given as one character = h (for  manhour) (Chap 3.9.5.2.1.9, Para 2.3.1.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00073" />
                  <objectPath allowedObjectFlag="0">//supportEquipDescr[attribute::materialUsage="mu01" or attribute::materialUsage="mu02" or attribute::materialUsage ="mu03" or attribute::materialUsage ="mu04" or attribute::materialUsage ="mu06"]</objectPath>
                  <objectUse>A support equipment can not be discarded, retained, modified, or referenced (Chap 3.9.5.2.1.9, Para 2.5.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00261"/>
                  <objectPath allowedObjectFlag="0">//preliminaryRqmts[descendant::materialSetRef[not(following-sibling::embeddedSupportEquipDescr or following-sibling::embeddedSupplyDescr or following-sibling::embeddedSpareDescr) ]]</objectPath>
                  <objectUse>Element materialSetRef must not be used in preliminary requirements without either a supplementary element embeddedSupportEquipDescr, embeddedSupplyDescr or embeddedSpareDescr (Chap 3.9.5.2.1.9, Para 2.5.1.3.8)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00074 " />
                  <objectPath allowedObjectFlag="0">//supplyDescr[attribute::materialUsage ="mu03" or attribute::materialUsage ="mu06"]</objectPath>
                  <objectUse>A supply can not be modified (Chap 3.9.5.2.1.9, Para 2.6.1).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.1.10 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00071" />
                  <objectPath allowedObjectFlag="0">//circuitBreakerRef[attribute::circuitBreakerAction="open-order" or attribute::circuitBreakerAction="close-order"]</objectPath>
                  <objectUse >The open-order and close-order values are not allowed for the attribute circuitBreakerAction in the circuitBreakerRef element. (Chap 3.9.5.2.1.10, Para 2.1.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00075" />
                  <objectPath allowedObjectFlag="0">//quantity[attribute::quantityType="qty02" and string-length(attribute::quantityTypeSpecifics)!= 3]</objectPath>
                  <objectUse>When the quantity type is price then a currency code must be provided iaw ISO 4217 (Chap 3.9.5.2.1.10, Para 2.1.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00076" />
                  <objectPath allowedObjectFlag="0">//quantity[attribute::quantityType="qty02"  and descendant-or-self::*[attribute::quantityUnitOfMeasure]]</objectPath>
                  <objectUse>When the quantity type is price then unit of measure does not apply (only currency code does) (Chap 3.9.5.2.1.10, Para 2.1.4).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.1.13 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00133" />
                  <objectPath allowedObjectFlag="0">//applicRef/dmRef/dmRefIdent/dmCode[attribute::infoCode!="0A2"]</objectPath>
                  <objectUse >When applicability is externalized a proper CIR data module (using IC=0A2) must be referred by the element applicRef. (Chap 3.9.5.2.1.13, Para 2.1)</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.2 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00077" />
                  <objectPath allowedObjectFlag="2">//levelledPara</objectPath>
                  <objectUse>The depth of the /levelledPara/ structure is unlimited, however, it is recommended to not exceed five levels of depth (Chap 3.9.5.2.2, Para 2.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00078" />
                  <objectPath allowedObjectFlag="0">//levelledPara[count(ancestor-or-self::levelledPara) > 8]</objectPath>
                  <objectUse>The subparagraph breakdown must never exceed eight levels of depth. (Chap 3.9.5.2.2, Para 2.4).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.3 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00079" />
                  <objectPath allowedObjectFlag="2">//proceduralStep</objectPath>
                  <objectUse>It is highly discouraged to exceed five levels of depth for proceduralStep. (Chap 3.9.5.2.3, Para 2.4.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00080" />
                  <objectPath allowedObjectFlag="0">//proceduralStep[count(ancestor-or-self::proceduralStep) > 8]</objectPath>
                  <objectUse>The procedural substep breakdown must never exceed eight levels of depth (Chap 3.9.5.2.3, Para 2.4.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00264" />
                  <objectPath allowedObjectFlag="0">//proceduralStep[child::title and (ancestor::proceduralStep[1][not(child::title)] or (ancestor::*[self::proceduralStep or self::mainProcedure])[1][child::*[self::proceduralStep[not(child::title)] or self::proceduralStepAlts[child::proceduralStep[not(child::title)]]]])]</objectPath>
                  <objectUse>A step must have a title if any of its sibling steps have a title and a substep can have title only if its parent step has a title. (Chap 3.9.5.2.3, Para 2.4.3).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.7 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00081" />
                  <objectPath allowedObjectFlag="0">//catalogSeqNumberRef[attribute::figureNumberVariant and normalize-space(attribute::figureNumberVariant) = ""]</objectPath>
                  <objectUse>The attribute figureNumberVariant must not be empty or contain a space string (Chap 3.9.5.2.7, Para 2.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00082" />
                  <objectPath allowedObjectFlag="0">//catalogSeqNumberRef[attribute::itemVariant and normalize-space(attribute::itemVariant) = ""]</objectPath>
                  <objectUse>The attribute itemVariant must not be empty or contain a space string (Chap 3.9.5.2.7, Para 2.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00083" />
                  <objectPath allowedObjectFlag="0">//catalogSeqNumber[attribute::figureNumberVariant and normalize-space(attribute::figureNumberVariant) = ""]</objectPath>
                  <objectUse>The attribute figureNumberVariant must not be empty or contain a space string (Chap 3.9.5.2.7, Para 2.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00084" />
                  <objectPath allowedObjectFlag="0">//catalogSeqNumber[attribute::itemVariant and normalize-space(attribute::itemVariant) = ""]</objectPath>
                  <objectUse>The attribute itemVariant must not be empty or contain a space string (Chap 3.9.5.2.7, Para 2.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00085" />
                  <objectPath allowedObjectFlag="0">//categoryOneContainerLocation[attribute::figureNumberVariant and normalize-space(attribute::figureNumberVariant) = ""]</objectPath>
                  <objectUse>The attribute figureNumberVariant must not be empty or contain a space string (Chap 3.9.5.2.7, Para 2.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00086" />
                  <objectPath allowedObjectFlag="0">//categoryOneContainerLocation[attribute::itemVariant and normalize-space(attribute::itemVariant) = ""]</objectPath>
                  <objectUse>The attribute itemVariant must not be empty or contain a space string (Chap 3.9.5.2.7, Para 2.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00087" />
                  <objectPath allowedObjectFlag="0">//catalogSeqNumber[(attribute::systemCode or attribute::subSystemCode or attribute::subSubSystemCode or attribute::assyCode) and (not(attribute::systemCode) or not(attribute::subSystemCode) or not(attribute::subSubSystemCode) or not(attribute::assyCode))]</objectPath>
                  <objectUse>A chapterized catalogue sequence number must contain all SNS derived constituents (Chap 3.9.5.2.7, Para 2.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00088" />
                  <objectPath allowedObjectFlag="0">//categoryOneContainerLocation[(attribute::systemCode or attribute::subSystemCode or attribute::subSubSystemCode or attribute::assyCode) and (not(attribute::systemCode) or not(attribute::subSystemCode) or not(attribute::subSubSystemCode) or not(attribute::assyCode))]</objectPath>
                  <objectUse>A chapterized catalogue sequence number must contain all SNS derived constituents (Chap 3.9.5.2.7, Para 2.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00089" />
                  <objectPath allowedObjectFlag="0">//catalogSeqNumberRef[(attribute::systemCode or attribute::subSystemCode or attribute::subSubSystemCode or attribute::assyCode) and (not(attribute::systemCode) or not(attribute::subSystemCode) or not(attribute::subSubSystemCode) or not(attribute::assyCode))]</objectPath>
                  <objectUse>A chapterized catalogue sequence number reference must contain all SNS derived constituents (Chap 3.9.5.2.7, Para 2.4.2.2).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00091" />
                  <objectPath allowedObjectFlag="0">//unitOfIssueQualificationSegment[not(preceding-sibling::unitOfIssue)]</objectPath>
                  <objectUse>unitOfIssueQualificationSegment must not be given if unitOfIssue is not used. (Chap 3.9.5.2.7, Para 2.5.5.3.9).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.11.13 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00092" />
                  <objectPath allowedObjectFlag="0">//warningSpec/warningAndCautionPara/internalRef</objectPath>
                  <objectUse >The element internalRef is not allowed in a warning in the Warning Repository data module since the warnings in the repository must be context insensitive (Chap 3.9.5.2.11.13, Para 2.1.1)</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.11.14 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00093" />
                  <objectPath allowedObjectFlag="0">//cautionSpec/warningAndCautionPara/internalRef</objectPath>
                  <objectUse >The element internalRef is not allowed in a caution in the Caution Repository data module since the cautions in the repository must be context insensitive (Chap 3.9.5.2.11.14, Para 2.1.1)</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.15 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00094" />
                  <objectPath allowedObjectFlag="0">//sb[child::sbMaterialInfoContent[ancestor::dmodule[child::identAndStatusSection[child::dmAddress[child::dmIdent[child::dmCode[attribute::infoCode!="934"]]]]]]]</objectPath>
                  <objectUse>When element sbMaterialInfoContent is used as direct child of element sb then the information code of the data module must be 934 (Chap 3.9.5.2.15, Para 2.3.1)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00095" />
                  <objectPath allowedObjectFlag="0">//sbRevisionInfo/sbTopic[attribute::sbTopicType="sbtt02" or attribute::sbTopicType="sbtt03" or attribute::sbTopicType="sbtt04"]</objectPath>
                  <objectUse >Generic values 'sbtt02', 'sbtt03' and 'sbtt04' are not allowed in service bulletin revision information (Chap 3.9.5.2.15, Para 2.3.3 and Para 2.3.3.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00096" />
                  <objectPath allowedObjectFlag="0">//sbSummary/sbTopic[attribute::sbTopicType="sbtt01" or attribute::sbTopicType="sbtt03" or attribute::sbTopicType="sbtt04"]</objectPath>
                  <objectUse >Generic values 'sbtt01', 'sbtt03' and 'sbtt04' are not allowed in service bulletin summary (Chap 3.9.5.2.15, Para 2.3.4 and Para 2.3.3.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00097" />
                  <objectPath allowedObjectFlag="0">//sbPlanningInfo/sbTopic[attribute::sbTopicType="sbtt01" or attribute::sbTopicType="sbtt02" or attribute::sbTopicType="sbtt04"]</objectPath>
                  <objectUse >Generic values 'sbtt01', 'sbtt02' and 'sbtt04' are not allowed in service bulletin planning information (Chap 3.9.5.2.15, Para 2.3.5 and Para 2.3.3.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00098" />
                  <objectPath allowedObjectFlag="0">//sbAdditionalInfo/sbTopic[attribute::sbTopicType="sbtt01" or attribute::sbTopicType="sbtt02" or attribute::sbTopicType="sbtt03"]</objectPath>
                  <objectUse >Generic values 'sbtt01', 'sbtt02' and 'sbtt03' are not allowed in service bulletin additional information (Chap 3.9.5.2.15, Para 2.3.8 and Para 2.3.3.2)</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.15.1 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00099" />
                  <objectPath allowedObjectFlag="0">//sbTimeCompliance[attribute::sbTimeComplianceType = "sbtct01" and child::limit[attribute::limitTypeValue != "po"]]</objectPath>
                  <objectUse>A basic service bulletin accomplishment limit has to be considered once (Chap 3.9.5.2.15.1, Para 2.6.1.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00100" />
                  <objectPath allowedObjectFlag="0">//sbTimeCompliance[attribute::sbTimeComplianceType = "sbtct02" and child::limit[attribute::limitTypeValue != "po"]]</objectPath>
                  <objectUse>A grace period limit has to be considered once (Chap 3.9.5.2.15.1, Para 2.6.1.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00101" />
                  <objectPath allowedObjectFlag="0">//sbTimeCompliance[attribute::sbTimeComplianceType = "sbtct03" and child::limit[attribute::limitTypeValue != "pe"]]</objectPath>
                  <objectUse>A repetitive inspection must be in accordance with periodic limit type (Chap 3.9.5.2.15.1, Para 2.6.1.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00102" />
                  <objectPath allowedObjectFlag="0">//sbDuration[child::quantity[descendant-or-self::*[attribute::quantityUnitOfMeasure != "h" and attribute::quantityUnitOfMeasure != "d" ]]]</objectPath>
                  <objectUse>The quantity unit of measure for service bulletin duration time must be given as one of the two characters h or d  (for  hour and day).(Chap 3.9.5.2.15.1, Para 2.7.1.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00103" />
                  <objectPath allowedObjectFlag="0">//sbEstimatedTime[child::quantity[descendant-or-self::*[attribute::quantityUnitOfMeasure != "h"]]]</objectPath>
                  <objectUse>The quantity unit of measure for service bulletin estimated time must be given as one character = h (for  manhour) (Chap 3.9.5.2.15.1, Para 2.7.1.3)</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.15.2 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00104" />
                  <objectPath allowedObjectFlag="0">//sbIndividualRemovedSpare/sbRemovedSpareDescr[attribute::materialUsage="mu03" or attribute::materialUsage="mu04" or @materialUsage ="mu05"]</objectPath>
                  <objectUse>A removed spare can not be modified from another spare, referenced or set of removed material (Chap 3.9.5.2.15.2, Para 2.5.2.1)</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.2.16 -->
                <structureObjectRule reasonForUpdateRefIds="CPF2019-007NN" changeType="add" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00271" />
                  <objectPath allowedObjectFlag="0">//dmodule[descendant::frontMatter and descendant::*[attribute::controlAuthorityRefs]]</objectPath>
                  <objectUse>The attribute controlAuthorityRefs must not be used in front matter (Chap 3.9.5.2.16, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00105" />
                  <objectPath allowedObjectFlag="0">//frontMatter[descendant-or-self::natoStockNumber[attribute::id]]</objectPath>
                  <objectUse>Attribute @id must not be used for element natoStockNumber in a front matter title page (Chap 3.9.5.2.16, Para 2.3.1.6.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00106" />
                  <objectPath allowedObjectFlag="0">//frontMatter[descendant-or-self::natoStockNumber[child::refs]]</objectPath>
                  <objectUse>Element refs must not be used natoStockNumber in a front matter title page (Chap 3.9.5.2.16, Para 2.3.1.6.4).</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="EPWG3" changeType="modify" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00107" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTitlePage[child::productAndModel[descendant-or-self::identNumber[child::refs]]]]</objectPath>
                  <objectUse>Element refs must not be used in identNumber in a front matter title page (Chap 3.9.5.2.16, Para 2.3.1.6.5).</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2019-007NN" changeType="modify" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00108" />
                  <objectPath allowedObjectFlag="0">//frontMatter[descendant-or-self::dataRestrictions[attribute::id]]</objectPath>
                  <objectUse>Attribute @id must not be used for element dataRestrictions in a front matter data module (Chap 3.9.5.2.16, Para 2.3.1.9)</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2019-007NN" changeType="modify" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00109" />
                  <objectPath allowedObjectFlag="0">//frontMatter[descendant-or-self::graphic[attribute::id]]</objectPath>
                  <objectUse>Attribute @id must not be used for element graphic in a front matter data module (Chap 3.9.5.2.16, Para 2.3.1.10)</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2019-007NN" changeType="modify" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00110" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTitlePage[child::enterpriseSpec[attribute::id]]]</objectPath>
                  <objectUse>Attribute @id must not be used for element enterpriseSpec in a front matter title page (Chap 3.9.5.2.16, Para 2.3.1.11)</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2019-007NN" changeType="modify" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00111" />
                  <objectPath allowedObjectFlag="0">//frontMatter[descendant-or-self::symbol[attribute::id]]</objectPath>
                  <objectUse>Attribute @id must not be used for element symbol in a front matter data module (Chap 3.9.5.2.16, Para 2.3.1.12)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00112" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTitlePage[child::responsiblePartnerCompany[attribute::id]]]</objectPath>
                  <objectUse>Attribute @id must not be used for element responsiblePartnerCompany in a front matter data module (Chap 3.9.5.2.16, Para 2.3.1.13)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00113" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTitlePage[child::responsiblePartnerCompany[not(descendant-or-self::enterpriseName)]]]</objectPath>
                  <objectUse>A front matter title page must always specify the name of the publisher (Chap 3.9.5.2.16, Para 2.3.1.12)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00115" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTitlePage[child::barCode[attribute::applicRefId]]]</objectPath>
                  <objectUse>Attribute @applicRefId must not be used for element barCode in a front matter title page (Chap 3.9.5.2.16, Para 2.3.1.15)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00116" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTitlePage[child::barCode[child::barCodeSymbol[attribute::id]]]]</objectPath>
                  <objectUse>Attribute @id must not be used for element barCodeSymbol in a front matter title page (Chap 3.9.5.2.16, Para 2.3.1.15.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00117" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTitlePage[child::frontMatterInfo[child::reducedPara[child::reducedRandomList[attribute::* and attribute::listItemPrefix!="pf03"]]]]]</objectPath>
                  <objectUse>No attributes must be used for element reducedRandomList in a front matter title page except attribute @listItemPrefix which must be set to value pf03 (Chap 3.9.5.2.16, Para 2.3.1.16.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2019-007NN" changeType="modify" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00118" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTitlePage[child::frontMatterInfo[descendant-or-self::reducedRandomListItem[attribute::id or attribute::applicRefId]]]] </objectPath>
                  <objectUse>Attributes @id and @applicRefId must not be used for element reducedRandomListItem in a front matter title page (Chap 3.9.5.2.16, Para 2.3.1.16.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00119" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTitlePage[child::frontMatterInfo[descendant-or-self::reducedRandomListItem[child::reducedListItemPara[attribute::id or attribute::applicRefId]]]]] </objectPath>
                  <objectUse>Attributes @id and @applicRefId must not be used for element reducedListItemPara in a front matter title page (Chap 3.9.5.2.16, Para 2.3.1.16.2)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00120" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTableOfContent[descendant-or-self::tocEntry[child::dmRef[not(descendant-or-self::techName and descendant-or-self::infoName)]]]]</objectPath>
                  <objectUse>References to data modules from the front matter tables of content must include a complete data module title (techName + infoName) as it must be presented (Chap 3.9.5.2.16, Para 2.3.2.3.3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00121" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTableOfContent[descendant-or-self::tocEntry[child::dmRef[not(attribute::applicRefId)]]]]</objectPath>
                  <objectUse>References to data modules in the front matter tables of content must include applicability information. (Chap 3.9.5.2.16, Para 2.3.2.3.3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00122" />
                  <objectPath allowedObjectFlag="0">//frontMatter[descendant-or-self::dmRef[attribute::referredFragment]]</objectPath>
                  <objectUse>References to other data modules from a front matter data module must be to data modules themselves, not to any specific part of them (Chap 3.9.5.2.16, Para 2.3.2.3.3)</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2019-007NN" changeType="modify" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00123" />
                  <objectPath allowedObjectFlag="0">//frontMatter[descendant-or-self::dmRef[attribute::id]]</objectPath>
                  <objectUse>Attribute @id must not be used for  references to other data modules in a front matter data module (Chap 3.9.5.2.16, Para 2.3.2.3.3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00124" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTableOfContent[descendant-or-self::tocEntry[child::pmRef[not(descendant-or-self::pmTitle)]]]]</objectPath>
                  <objectUse>References to publication modules in the front matter tables of content must include a full title (pmTitle) as it must be presented (Chap 3.9.5.2.16, Para 2.3.2.3.4)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00125" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTableOfContent[descendant-or-self::tocEntry[child::pmRef[not(attribute::applicRefId)]]]]</objectPath>
                  <objectUse>References to publication modules in the front matter tables of content must include applicability information. (Chap 3.9.5.2.16, Para 2.3.2.3.4)</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2019-007NN" changeType="modify" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00126" />
                  <objectPath allowedObjectFlag="0">//frontMatter[descendant-or-self::pmRef[attribute::id]]</objectPath>
                  <objectUse>Attribute @id must not be used for  references to publication modules in a front matter data module  (Chap 3.9.5.2.16, Para 2.3.2.3.4)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00127" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTableOfContent[descendant-or-self::tocEntry[child::externalPubRef[not(descendant-or-self::externalPubCode)]]]]</objectPath>
                  <objectUse>References to external publications in the front matter tables of content must include the external publication code (externalPubCode) as it must be presented (Chap 3.9.5.2.16, Para 2.3.2.3.5)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00128" />
                  <objectPath allowedObjectFlag="0">//frontMatter[child::frontMatterTableOfContent[descendant-or-self::tocEntry[child::externalPubRef[not(attribute::applicRefId)]]]]</objectPath>
                  <objectUse>References to external publications in the front matter tables of content must include applicability information. (Chap 3.9.5.2.16, Para 2.3.2.3.5)</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2019-007NN" changeType="modify" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00129" />
                  <objectPath allowedObjectFlag="0">//frontMatter[descendant-or-self::externalPubRef[attribute::id]]</objectPath>
                  <objectUse>Attribute @id must not be used for  references to external publications in a front matter data module  (Chap 3.9.5.2.16, Para 2.3.2.3.5)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00130" />
                  <objectPath allowedObjectFlag="0">//frontMatter[descendant-or-self::footnote[descendant-or-self::para[child::sequentialList or child::randomList or child::definitionList]]]</objectPath>
                  <objectUse>Front matter foot notes must not contain lists (Chap 3.9.5.2.16, Para 2.3.3.3)</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.3 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00131" />
                  <objectPath allowedObjectFlag="0">//dmAddress[child::dmIdent[child::dmCode[attribute::infoCode="0A2" and ancestor::dmodule[descendant::applicRef]]]]</objectPath>
                  <objectUse>The element applicRef must not be used in the applicability Repository (Chap 3.9.5.3, Para 2.1.3).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00132" />
                  <objectPath allowedObjectFlag="0">//dmAddress[child::dmIdent[child::dmCode[attribute::infoCode="0A2" and ancestor::dmodule[descendant::referencedApplicGroupRef]]]]</objectPath>
                  <objectUse>The element referencedApplicGroupRef must not be used in the applicability Repository (Chap 3.9.5.3, Para 2.1.3).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00134" />
                  <objectPath allowedObjectFlag="0">//expression[ancestor::dmodule[descendant::commonRepository and descendant::identAndStatusSection[child::dmAddress[child::dmIdent[child::dmCode[attribute::infoCode="0A2"]]]]] and not(ancestor::referencedApplicGroup)]</objectPath>
                  <objectUse >In an applicability repository, the element expression (child of element applic) is used only in the referencedApplicGroup element (Chap 3.9.5.3, Para 2.4)</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.3.1 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00135" />
                  <objectPath allowedObjectFlag="0">//productAttribute[attribute::valuePattern and (attribute::valueDataType = "boolean" or attribute::valueDataType = "integer" or attribute::valueDataType = "real")]</objectPath>
                  <objectUse>A product attribute value pattern is not allowed when the product attribute value data type is boolean, integer or real (Chap 3.9.5.3.1, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00136" />
                  <objectPath allowedObjectFlag="0">//productAttribute[child::enumeration[attribute::enumerationLabel and attribute::applicPropertyValues[contains(string(.),"~") or contains(string(.),"|")]]]</objectPath>
                  <objectUse>Enumerations with enumerationLabel attribute cannot specify a range or list of values in applicPropertyValues attribute (Chap 3.9.5.3.1, Para 2.3.3.4).</objectUse>
                </structureObjectRule>
                <!-- 3.9.5.3.2 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00137" />
                  <objectPath allowedObjectFlag="0">//condType[attribute::valuePattern and (attribute::valueDataType = "boolean" or attribute::valueDataType = "integer" or attribute::valueDataType = "real")]</objectPath>
                  <objectUse>A condition type value pattern is not allowed when the product attribute value data type is boolean, integer or real (Chap 3.9.5.3.2, Para 2.3.1).</objectUse>
                </structureObjectRule>
                <!-- 4.3.8 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00138" />
                  <objectPath allowedObjectFlag="2">//@itemLocationCode</objectPath>
                  <objectUse>Attribute itemLocationCode - Data module item location (Chap 4.3.8, Para 2)</objectUse>
                  <objectValue valueForm="single" valueAllowed="XA" valueTailoring="lexical">Information related to items installed on the Product</objectValue>
                  <objectValue valueForm="single" valueAllowed="XB" valueTailoring="lexical">Information related to items installed on a major assembly removed from the Product</objectValue>
                  <objectValue valueForm="single" valueAllowed="XC" valueTailoring="lexical">Information related to items on the bench</objectValue>
                  <objectValue valueForm="single" valueAllowed="XD" valueTailoring="lexical">Information related to all three locations A, B, and C</objectValue>
                  <objectValue valueForm="single" valueAllowed="XT" valueTailoring="lexical">Information related to training</objectValue>
                </structureObjectRule>
                <!-- 4.3.9 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00139" />
                  <objectPath allowedObjectFlag="2">//@learnCode</objectPath>
                  <objectUse >Attribute learnCode - the first character of the learn code must be set to either "H" for human performance technology codes or "T" for training codes (Chap 4.3.9, Para 2; Chap 8.5, Para 2).</objectUse>
                  <objectValue valueForm="single" valueAllowed="H10" valueTailoring="restrictable">Performance analysis</objectValue>
                  <objectValue valueForm="single" valueAllowed="H11" valueTailoring="restrictable">Organizational analysis - Vision statement</objectValue>
                  <objectValue valueForm="single" valueAllowed="H12" valueTailoring="restrictable">Organizational analysis - Mission statement</objectValue>
                  <objectValue valueForm="single" valueAllowed="H13" valueTailoring="restrictable">Organizational analysis - Values</objectValue>
                  <objectValue valueForm="single" valueAllowed="H14" valueTailoring="restrictable">Organizational analysis - Goal statement</objectValue>
                  <objectValue valueForm="single" valueAllowed="H15" valueTailoring="restrictable">Organizational analysis - Objective statement</objectValue>
                  <objectValue valueForm="single" valueAllowed="H16" valueTailoring="restrictable">Organizational analysis - Gap statement</objectValue>
                  <objectValue valueForm="single" valueAllowed="H17" valueTailoring="restrictable">Environmental analysis - Organizational environment</objectValue>
                  <objectValue valueForm="single" valueAllowed="H18" valueTailoring="restrictable">Environmental analysis - Work environment</objectValue>
                  <objectValue valueForm="single" valueAllowed="H19" valueTailoring="restrictable">Performer analysis - Worker</objectValue>
                  <objectValue valueForm="single" valueAllowed="H20" valueTailoring="restrictable">Cause Analysis</objectValue>
                  <objectValue valueForm="single" valueAllowed="H21" valueTailoring="restrictable">Environmental factor</objectValue>
                  <objectValue valueForm="single" valueAllowed="H22" valueTailoring="restrictable">Internal factor</objectValue>
                  <objectValue valueForm="single" valueAllowed="H30" valueTailoring="restrictable">Intervention definition</objectValue>
                  <objectValue valueForm="single" valueAllowed="H31" valueTailoring="restrictable">Performance support</objectValue>
                  <objectValue valueForm="single" valueAllowed="H32" valueTailoring="restrictable">Job/Work design</objectValue>
                  <objectValue valueForm="single" valueAllowed="H33" valueTailoring="restrictable">Personal development</objectValue>
                  <objectValue valueForm="single" valueAllowed="H34" valueTailoring="restrictable">Human resource development</objectValue>
                  <objectValue valueForm="single" valueAllowed="H35" valueTailoring="restrictable">Organizational communication</objectValue>
                  <objectValue valueForm="single" valueAllowed="H36" valueTailoring="restrictable">Organizational design and development</objectValue>
                  <objectValue valueForm="single" valueAllowed="H37" valueTailoring="restrictable">Training</objectValue>
                  <objectValue valueForm="single" valueAllowed="H40" valueTailoring="restrictable">Intervention implementation</objectValue>
                  <objectValue valueForm="single" valueAllowed="H50" valueTailoring="restrictable">Evaluation</objectValue>
                  <objectValue valueForm="single" valueAllowed="H51" valueTailoring="restrictable">Formative analysis</objectValue>
                  <objectValue valueForm="single" valueAllowed="H52" valueTailoring="restrictable">Summative - Immediate performance competence</objectValue>
                  <objectValue valueForm="single" valueAllowed="H53" valueTailoring="restrictable">Summative - Job transfer</objectValue>
                  <objectValue valueForm="single" valueAllowed="H54" valueTailoring="restrictable">Summative - Organizational impact/ROI</objectValue>
                  <objectValue valueForm="single" valueAllowed="T10" valueTailoring="restrictable">Attention</objectValue>
                  <objectValue valueForm="single" valueAllowed="T11" valueTailoring="restrictable">Perceptual - Concrete example</objectValue>
                  <objectValue valueForm="single" valueAllowed="T12" valueTailoring="restrictable">Perceptual - Incongruity/Conflict</objectValue>
                  <objectValue valueForm="single" valueAllowed="T13" valueTailoring="restrictable">Inquiry - Incongruity/Conflict</objectValue>
                  <objectValue valueForm="single" valueAllowed="T14" valueTailoring="restrictable">Inquiry - Participatory exercise</objectValue>
                  <objectValue valueForm="single" valueAllowed="T15" valueTailoring="restrictable">Inquiry - Relevance</objectValue>
                  <objectValue valueForm="single" valueAllowed="T20" valueTailoring="restrictable">Learning objectives</objectValue>
                  <objectValue valueForm="single" valueAllowed="T21" valueTailoring="restrictable">Terminal objective - Intellectual skill - Discriminations</objectValue>
                  <objectValue valueForm="single" valueAllowed="T22" valueTailoring="restrictable">Terminal objective - Intellectual skill - Concepts</objectValue>
                  <objectValue valueForm="single" valueAllowed="T23" valueTailoring="restrictable">Terminal objective - Intellectual skill - Rules/Principles</objectValue>
                  <objectValue valueForm="single" valueAllowed="T24" valueTailoring="restrictable">Terminal objective - Intellectual skill - Processes</objectValue>
                  <objectValue valueForm="single" valueAllowed="T25" valueTailoring="restrictable">Terminal objective - Intellectual skill - Procedures</objectValue>
                  <objectValue valueForm="single" valueAllowed="T26" valueTailoring="restrictable">Terminal objective - Intellectual skill - Higher order rules</objectValue>
                  <objectValue valueForm="single" valueAllowed="T27" valueTailoring="restrictable">Terminal objective - Verbal information - Facts</objectValue>
                  <objectValue valueForm="single" valueAllowed="T28" valueTailoring="restrictable">Terminal objective - Motor skill</objectValue>
                  <objectValue valueForm="single" valueAllowed="T29" valueTailoring="restrictable">Enabling objective - Intellectual skill- Discriminations</objectValue>
                  <objectValue valueForm="single" valueAllowed="T2A" valueTailoring="restrictable">Enabling objective - Intellectual skill- Concepts</objectValue>
                  <objectValue valueForm="single" valueAllowed="T2B" valueTailoring="restrictable">Enabling objective - Intellectual skill- Rules/Principles</objectValue>
                  <objectValue valueForm="single" valueAllowed="T2C" valueTailoring="restrictable">Enabling objective - Intellectual skill- Processes</objectValue>
                  <objectValue valueForm="single" valueAllowed="T2D" valueTailoring="restrictable">Enabling objective - Intellectual skill- Procedures</objectValue>
                  <objectValue valueForm="single" valueAllowed="T2E" valueTailoring="restrictable">Enabling objective - Intellectual skill- Higher order rules</objectValue>
                  <objectValue valueForm="single" valueAllowed="T2F" valueTailoring="restrictable">Enabling objective - Verbal information - Facts</objectValue>
                  <objectValue valueForm="single" valueAllowed="T2G" valueTailoring="restrictable">Enabling objective - Motor skill</objectValue>
                  <objectValue valueForm="single" valueAllowed="T30" valueTailoring="restrictable">Recall</objectValue>
                  <objectValue valueForm="single" valueAllowed="T31" valueTailoring="restrictable">Analogy</objectValue>
                  <objectValue valueForm="single" valueAllowed="T32" valueTailoring="restrictable">Demonstration</objectValue>
                  <objectValue valueForm="single" valueAllowed="T33" valueTailoring="restrictable">Informative practice</objectValue>
                  <objectValue valueForm="single" valueAllowed="T34" valueTailoring="restrictable">Comparative organizer</objectValue>
                  <objectValue valueForm="single" valueAllowed="T35" valueTailoring="restrictable">Metaphoric device</objectValue>
                  <objectValue valueForm="single" valueAllowed="T36" valueTailoring="restrictable">Prerequisite concept review</objectValue>
                  <objectValue valueForm="single" valueAllowed="T37" valueTailoring="restrictable">Question/problem</objectValue>
                  <objectValue valueForm="single" valueAllowed="T38" valueTailoring="restrictable">Similar-task review</objectValue>
                  <objectValue valueForm="single" valueAllowed="T40" valueTailoring="restrictable">Content</objectValue>
                  <objectValue valueForm="single" valueAllowed="T41" valueTailoring="restrictable">Static content - Discrimination Expositive</objectValue>
                  <objectValue valueForm="single" valueAllowed="T42" valueTailoring="restrictable">Static content - Fact Expositive</objectValue>
                  <objectValue valueForm="single" valueAllowed="T43" valueTailoring="restrictable">Static content - Concept Expositive</objectValue>
                  <objectValue valueForm="single" valueAllowed="T44" valueTailoring="restrictable">Static content - Rule/Principle Expositive</objectValue>
                  <objectValue valueForm="single" valueAllowed="T45" valueTailoring="restrictable">Static content - Procedure Expositive</objectValue>
                  <objectValue valueForm="single" valueAllowed="T46" valueTailoring="restrictable">Static content - Higher order rule Expositive</objectValue>
                  <objectValue valueForm="single" valueAllowed="T47" valueTailoring="restrictable">Static content - Processes Expositive</objectValue>
                  <objectValue valueForm="single" valueAllowed="T48" valueTailoring="restrictable">Animated content - Discrimination</objectValue>
                  <objectValue valueForm="single" valueAllowed="T49" valueTailoring="restrictable">Animated content - Fact</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4A" valueTailoring="restrictable">Animated content - Concept</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4B" valueTailoring="restrictable">Animated content - Rule/Principle</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4C" valueTailoring="restrictable">Animated content - Procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4D" valueTailoring="restrictable">Animated content - Higher order rule</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4E" valueTailoring="restrictable">Animated content - Processes</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4F" valueTailoring="restrictable">Interactive content - Discrimination</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4R" valueTailoring="restrictable">Interactive content - Fact</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4G" valueTailoring="restrictable">Interactive content - Concept</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4H" valueTailoring="restrictable">Interactive content - Rule/Principle</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4J" valueTailoring="restrictable">Interactive content - Procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4K" valueTailoring="restrictable">Interactive content - Higher order rule</objectValue>
                  <objectValue valueForm="single" valueAllowed="T4L" valueTailoring="restrictable">Interactive content - Processes</objectValue>
                  <objectValue valueForm="single" valueAllowed="T50" valueTailoring="restrictable">Learning guidance</objectValue>
                  <objectValue valueForm="single" valueAllowed="T51" valueTailoring="restrictable">Analogy</objectValue>
                  <objectValue valueForm="single" valueAllowed="T52" valueTailoring="restrictable">Metaphoric device</objectValue>
                  <objectValue valueForm="single" valueAllowed="T53" valueTailoring="restrictable">Drill and practice/informative practice</objectValue>
                  <objectValue valueForm="single" valueAllowed="T54" valueTailoring="restrictable">Case study</objectValue>
                  <objectValue valueForm="single" valueAllowed="T55" valueTailoring="restrictable">Comparative organizer</objectValue>
                  <objectValue valueForm="single" valueAllowed="T56" valueTailoring="restrictable">Concept map</objectValue>
                  <objectValue valueForm="single" valueAllowed="T57" valueTailoring="restrictable">Demonstration</objectValue>
                  <objectValue valueForm="single" valueAllowed="T58" valueTailoring="restrictable">Example/non-example</objectValue>
                  <objectValue valueForm="single" valueAllowed="T59" valueTailoring="restrictable">Game</objectValue>
                  <objectValue valueForm="single" valueAllowed="T5A" valueTailoring="restrictable">Mnemonic device</objectValue>
                  <objectValue valueForm="single" valueAllowed="T5B" valueTailoring="restrictable">Problem solving</objectValue>
                  <objectValue valueForm="single" valueAllowed="T5C" valueTailoring="restrictable">Simulation</objectValue>
                  <objectValue valueForm="single" valueAllowed="T5D" valueTailoring="restrictable">Story</objectValue>
                  <objectValue valueForm="single" valueAllowed="T60" valueTailoring="restrictable">Performance</objectValue>
                  <objectValue valueForm="single" valueAllowed="T61" valueTailoring="restrictable">Drag-and-drop/matching exercise</objectValue>
                  <objectValue valueForm="single" valueAllowed="T62" valueTailoring="restrictable">Multiple-choice - One selection</objectValue>
                  <objectValue valueForm="single" valueAllowed="T63" valueTailoring="restrictable">Multiple-choice -Multiple selection</objectValue>
                  <objectValue valueForm="single" valueAllowed="T64" valueTailoring="restrictable">Short answer free text Fill in the blank</objectValue>
                  <objectValue valueForm="single" valueAllowed="T65" valueTailoring="restrictable">Simulation</objectValue>
                  <objectValue valueForm="single" valueAllowed="T66" valueTailoring="restrictable">Game</objectValue>
                  <objectValue valueForm="single" valueAllowed="T70" valueTailoring="restrictable">Feedback</objectValue>
                  <objectValue valueForm="single" valueAllowed="T71" valueTailoring="restrictable">Knowledge of correct response</objectValue>
                  <objectValue valueForm="single" valueAllowed="T72" valueTailoring="restrictable">Knowledge of correct solution</objectValue>
                  <objectValue valueForm="single" valueAllowed="T73" valueTailoring="restrictable">Knowledge of consequence</objectValue>
                  <objectValue valueForm="single" valueAllowed="T80" valueTailoring="restrictable">Assessment</objectValue>
                  <objectValue valueForm="single" valueAllowed="T81" valueTailoring="restrictable">Drag-and-drop/matching exercise</objectValue>
                  <objectValue valueForm="single" valueAllowed="T82" valueTailoring="restrictable">Multiple-choice - One selection</objectValue>
                  <objectValue valueForm="single" valueAllowed="T83" valueTailoring="restrictable">Multiple-choice - Multiple selection</objectValue>
                  <objectValue valueForm="single" valueAllowed="T84" valueTailoring="restrictable">Short answer free text</objectValue>
                  <objectValue valueForm="single" valueAllowed="T85" valueTailoring="restrictable">Simulation</objectValue>
                  <objectValue valueForm="single" valueAllowed="T86" valueTailoring="restrictable">Game</objectValue>
                  <objectValue valueForm="single" valueAllowed="T87" valueTailoring="restrictable">Pre-test</objectValue>
                  <objectValue valueForm="single" valueAllowed="T88" valueTailoring="restrictable">Post-test</objectValue>
                  <objectValue valueForm="single" valueAllowed="T90" valueTailoring="restrictable">Retention and transfer</objectValue>
                  <objectValue valueForm="single" valueAllowed="T91" valueTailoring="restrictable">Drill and practice/informative practice</objectValue>
                  <objectValue valueForm="single" valueAllowed="T92" valueTailoring="restrictable">Case study</objectValue>
                  <objectValue valueForm="single" valueAllowed="T93" valueTailoring="restrictable">Comparative organizer</objectValue>
                  <objectValue valueForm="single" valueAllowed="T94" valueTailoring="restrictable">Demonstration</objectValue>
                  <objectValue valueForm="single" valueAllowed="T95" valueTailoring="restrictable">Example/non-example</objectValue>
                  <objectValue valueForm="single" valueAllowed="T96" valueTailoring="restrictable">Game</objectValue>
                  <objectValue valueForm="single" valueAllowed="T97" valueTailoring="restrictable">Problem solving</objectValue>
                  <objectValue valueForm="single" valueAllowed="T98" valueTailoring="restrictable">Simulation</objectValue>
                  <objectValue valueForm="single" valueAllowed="T99" valueTailoring="restrictable">Story</objectValue>
                </structureObjectRule>
                <!-- 4.3.10 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00140" />
                  <objectPath allowedObjectFlag="0">//dmCode[attribute::learnCode and not(attribute::learnEventCode)]</objectPath>
                  <objectUse>Whenever a learn code is used, the learn event code must be used (Chap 4.3.10, Para 1).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00141" />
                  <objectPath allowedObjectFlag="2">//@learnEventCode</objectPath>
                  <objectUse>Attribute learnEventCode - Used to store the learn event code (Chap 4.3.10, Para 2).</objectUse>
                  <objectValue valueForm="single" valueAllowed="A" valueTailoring="lexical">Learning plan</objectValue>
                  <objectValue valueForm="single" valueAllowed="B" valueTailoring="lexical">Learning overview</objectValue>
                  <objectValue valueForm="single" valueAllowed="C" valueTailoring="lexical">Learning content</objectValue>
                  <objectValue valueForm="single" valueAllowed="D" valueTailoring="lexical">Learning summary</objectValue>
                  <objectValue valueForm="single" valueAllowed="E" valueTailoring="lexical">Learning assessment</objectValue>
                </structureObjectRule>
                <!-- 4.4 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00142" />
                  <objectPath allowedObjectFlag="2">//@infoEntityIdent</objectPath>
                  <objectUse>ICN representing an illustration, symbol or other information object supporting a CSDB object (Chap 4.4, Para 2.1 and Para 2.2).</objectUse>
                  <objectValue valueForm="pattern" valueAllowed="((ICN-[A-Z0-9]{5}-[A-Z0-9]{5,10}-[0-9]{3}-[0-9]{2})|(ICN-[A-Z0-9]{2,14}-[A-Z0-9]{1,4}-[A-Z0-9]{6,9}-[A-Z0-9]{1}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z]{1}-[0-9]{2,3}-[0-9]{1,2}))">CAGE-based ICN (15-20 char) or MIC-based ICN (26-44 char)</objectValue>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00143" />
                  <objectPath allowedObjectFlag="2">//@infoEntityRefIdent</objectPath>
                  <objectUse>ICN representing an illustration, symbol or other information object supporting a CSDB object (Chap 4.4, Para 2.1 and Para 2.2).</objectUse>
                  <objectValue valueForm="pattern" valueAllowed="((ICN-[A-Z0-9]{5}-[A-Z0-9]{5,10}-[0-9]{3}-[0-9]{2})|(ICN-[A-Z0-9]{2,14}-[A-Z0-9]{1,4}-[A-Z0-9]{6,9}-[A-Z0-9]{1}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z]{1}-[0-9]{2,3}-[0-9]{1,2}))">CAGE-based ICN (15-20 char) or MIC-based ICN (26-44 char)</objectValue>
                </structureObjectRule>
                <!-- 4.7 -->
                <structureObjectRule reasonForUpdateRefIds="CPF2013-066EPWG" changeType="add" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00274" />
                  <objectPath allowedObjectFlag="0">//issueInfo/@issueNumber[1000 > number() and string-length() != 3]</objectPath>
                  <objectUse>If the value of the attribute issueNumber is less then 1000, use only three digits (Chap 4.7, Para 2.1).</objectUse>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2013-066EPWG" changeType="add" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00275" />
                  <objectPath allowedObjectFlag="0">//issueInfo/@issueNumber[10000 > number() and number() > 999 and string-length() != 4]</objectPath>
                  <objectUse>If the value of the attribute issueNumber is 1,000 to 9,999, use only four digits (Chap 4.7, Para 2.1).</objectUse>
                </structureObjectRule>
                <!-- 4.10.1 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00265"/>
                  <objectPath allowedObjectFlag="0">/dmodule[content/brDoc]/identAndStatusSection/dmAddress/dmIdent/dmCode[@infoCode!="024" or @itemLocationCode!="D"]</objectPath>
                  <objectUse>The information code for a business rules document data module is 024, and the item location code is set to D. (Chap 4.10.1, Para 3).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00266"/>
                  <objectPath allowedObjectFlag="0">/dmodule/content/brDoc//brDecision[(brDecisionText or brDecisionValueGroup) and not(@brDecisionIdentNumber)]</objectPath>
                  <objectUse>A business rules decision must be assigned a number using attribute brDecisionIdentNumber.  (Chap 4.10.1, Para 4.4).</objectUse>
                </structureObjectRule>
                <!-- 4.10.2.1 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00145" />
                  <objectPath allowedObjectFlag="2">//snsSystem/snsCode</objectPath>
                  <objectUse >The code representing the system level in an SNS, limited to 2 or 3 char. If three positions are used, the 1st position is the Material Item Category Code, restricted to values A-H, J, T-Z and 0-9. (Chap 4.3.3, Para 2.2.2 and Chap 4.10.2.1, Para 2.1.1).</objectUse>
                  <objectValue valueForm="pattern" valueAllowed="([A-HJT-Z0-9])?[A-Z0-9]{2}">System code is limited to 2 or 3 char.</objectValue>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00146" />
                  <objectPath allowedObjectFlag="2">//snsSubSystem/snsCode</objectPath>
                  <objectUse >The code representing the subsystem level in an SNS, limited to 1 char. (Chap 4.3.3, Para 2.2.3 and Chap 4.10.2.1, Para 2.1.2).</objectUse>
                  <objectValue valueForm="pattern" valueAllowed="[A-Z0-9]{1}">Subsystem code is limited to 1 char.</objectValue>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00147" />
                  <objectPath allowedObjectFlag="2">//snsSubSubSystem/snsCode</objectPath>
                  <objectUse >The code representing the sub-subsystem level in an SNS, limited to 1 char. (Chap 4.3.3, Para 2.2.3 and Chap 4.10.2.1, Para 2.1.3).</objectUse>
                  <objectValue valueForm="pattern" valueAllowed="[A-Z0-9]{1}">Sub-subsystem code is limited to 1 char.</objectValue>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00148" />
                  <objectPath allowedObjectFlag="2">//snsAssy/snsCode</objectPath>
                  <objectUse >The code representing the assembly code in an SNS, limited to 2 or 4 char. (Chap 4.3.3, Para 2.2.4 and Chap 4.10.2.1, Para 2.1.4).</objectUse>
                  <objectValue valueForm="pattern" valueAllowed="(([A-Z0-9]{2})|([A-Z0-9]{4}))">Assembly code is limited to 2 or 4 char.</objectValue>
                </structureObjectRule>
                <!-- 4.10.2.4 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00144" />
                  <objectPath allowedObjectFlag="0">/dmodule[content/brex]/identAndStatusSection/dmAddress/dmIdent/dmCode[@modelIdentCode!="S1000D" and @assyCode!="00" and @assyCode!="0000"]</objectPath>
                  <objectUse>SNS must not be applied below sub-subsystem level. Unit/Assembly of the BREX DM must be 00 or 0000. (Chap 4.10.2.4, Para 2).</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00268"/>
                  <objectPath allowedObjectFlag="0">/dmodule[content/brex]/identAndStatusSection/dmAddress/dmIdent/dmCode[@infoCode!="022" or @itemLocationCode!="D"]</objectPath>
                  <objectUse>The information code for a BREX data module is 022, and the item location code is set to D. (Chap 4.10.2.4, Para 2).</objectUse>
                </structureObjectRule>
                <!-- 4.13.3 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00150" />
                  <objectPath allowedObjectFlag="0">//accessPointAlts[child::accessPoint[following-sibling::accessPoint] and child::accessPoint[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an accessPoint alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00151" />
                  <objectPath allowedObjectFlag="0">//assocWarningMalfunctionAlts[child::assocWarningMalfunction[following-sibling::assocWarningMalfunction] and child::assocWarningMalfunction[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an assocWarningMalfunction alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00152" />
                  <objectPath allowedObjectFlag="0">//bitMessageAlts[child::bitMessage[following-sibling::bitMessage] and child::bitMessage[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an bitMessage alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00153" />
                  <objectPath allowedObjectFlag="0">//circuitBreakerAlts[child::circuitBreaker[following-sibling::circuitBreaker] and child::circuitBreaker[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an circuitBreaker alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00154" />
                  <objectPath allowedObjectFlag="0">//commonInfoDescrParaAlts[child::commonInfoDescrPara[following-sibling::commonInfoDescrPara] and child::commonInfoDescrPara[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an commonInfoDescrPara alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00155" />
                  <objectPath allowedObjectFlag="0">//correlatedFaultAlts[child::correlatedFault[following-sibling::correlatedFault] and child::correlatedFault[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an correlatedFault alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00156" />
                  <objectPath allowedObjectFlag="0">//detectedFaultAlts[child::detectedFault[following-sibling::detectedFault] and child::detectedFault[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an detectedFault alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00157" />
                  <objectPath allowedObjectFlag="0">//dialogAlts[child::dialog[following-sibling::dialog] and child::dialog[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an dialog alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00158" />
                  <objectPath allowedObjectFlag="0">//dmNodeAlts[child::dmNode[following-sibling::dmNode] and child::dmNode[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an dmNode alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00159" />
                  <objectPath allowedObjectFlag="0">//dmSeqAlts[child::dmSeq[following-sibling::dmSeq] and child::dmSeq[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an dmSeq alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00160" />
                  <objectPath allowedObjectFlag="0">//electricalEquipAlts[child::electricalEquip[following-sibling::electricalEquip] and child::electricalEquip[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an electricalEquip alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00161" />
                  <objectPath allowedObjectFlag="0">//figureAlts[child::figure[following-sibling::figure] and child::figure[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an figure alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00162" />
                  <objectPath allowedObjectFlag="0">//functionalItemAlts[child::functionalItem[following-sibling::functionalItem] and child::functionalItem[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an functionalItem alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00163" />
                  <objectPath allowedObjectFlag="0">//harnessAlts[child::harness[following-sibling::harness] and child::harness[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an harness alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00164" />
                  <objectPath allowedObjectFlag="0">//isolatedFaultAlts[child::isolatedFault[following-sibling::isolatedFault] and child::isolatedFault[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an isolatedFault alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00165" />
                  <objectPath allowedObjectFlag="0">//isolationProcedureEndAlts[child::isolationProcedureEnd[following-sibling::isolationProcedureEnd] and child::isolationProcedureEnd[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an isolationProcedureEnd alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00166" />
                  <objectPath allowedObjectFlag="0">//isolationStepAlts[child::isolationStep[following-sibling::isolationStep] and child::isolationStep[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an isolationStep alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00167" />
                  <objectPath allowedObjectFlag="0">//levelledParaAlts[child::levelledPara[following-sibling::levelledPara] and child::levelledPara[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an levelledPara alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00168" />
                  <objectPath allowedObjectFlag="0">//messageAlts[child::message[following-sibling::message] and child::message[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an message alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00169" />
                  <objectPath allowedObjectFlag="0">//multimediaAlts[child::multimedia[following-sibling::multimedia] and child::multimedia[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an multimedia alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00170" />
                  <objectPath allowedObjectFlag="0">//observedFaultAlts[child::observedFault[following-sibling::observedFault] and child::observedFault[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an observedFault alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00171" />
                  <objectPath allowedObjectFlag="0">//proceduralStepAlts[child::proceduralStep[following-sibling::proceduralStep] and child::proceduralStep[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an proceduralStep alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00172" />
                  <objectPath allowedObjectFlag="0">//supplyRqmtAlts[child::supplyRqmt[following-sibling::supplyRqmt] and child::supplyRqmt[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an supplyRqmt alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00173" />
                  <objectPath allowedObjectFlag="0">//taskDefinitionAlts[child::taskDefinition[following-sibling::taskDefinition] and child::taskDefinition[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an taskDefinition alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00174" />
                  <objectPath allowedObjectFlag="0">//toolAlts[child::tool[following-sibling::tool] and child::tool[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an tool alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00175" />
                  <objectPath allowedObjectFlag="0">//warningMalfunctionAlts[child::warningMalfunction[following-sibling::warningMalfunction] and child::warningMalfunction[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an warningMalfunction alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00176" />
                  <objectPath allowedObjectFlag="0">//wireAlts[child::wire[following-sibling::wire] and child::wire[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an wire alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00177" />
                  <objectPath allowedObjectFlag="0">//zoneAlts[child::zone[following-sibling::zone] and child::zone[not(attribute::applicRefId)]]</objectPath>
                  <objectUse>In an zone alternates group each alternate must specify a valid applicability annotation  (Chap 4.13.3, Para 2 and Para 3)</objectUse>
                </structureObjectRule>
                <!-- 6.2.2 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00178" />
                  <objectPath allowedObjectFlag="0">//randomList/listItem/para/randomList/listItem/para/randomList/listItem/para[child::randomList]</objectPath>
                  <objectUse>There are [at the most] three levels of random lists (Chap 6.2.2, Para 2.6.2.1).</objectUse>
                </structureObjectRule>
                <!-- 8.4.1 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00179" />
                  <objectPath allowedObjectFlag="2">//updateIdent/updateCode/@infoCode</objectPath>
                  <objectUse >Attribute infoCode - The data update file information code (Chap 8.4.1, Para 1).</objectUse>
                  <objectValue valueForm="single" valueAllowed="00E" valueTailoring="restrictable">Functional item numbers common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00F" valueTailoring="restrictable">Circuit breakers common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00G" valueTailoring="restrictable">Parts common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00H" valueTailoring="restrictable">Zones common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00J" valueTailoring="restrictable">Access panels and doors common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00K" valueTailoring="restrictable">Organizations common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00L" valueTailoring="restrictable">Supplies - List of products common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00M" valueTailoring="restrictable">Supplies - List of requirements common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00N" valueTailoring="restrictable">Support equipment common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00X" valueTailoring="restrictable">Controls and indicators common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="0A1" valueTailoring="restrictable">Functional and/or physical areas common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="0A2" valueTailoring="restrictable">Applicability repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="012" valueTailoring="restrictable">General warnings and cautions and related safety data</objectValue>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2017-014AA" changeType="modify">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00180" />
                  <objectPath allowedObjectFlag="2">//dmIdent/dmCode/@infoCode</objectPath>
                  <objectUse >Attribute infoCode - The data module information code (Chap 8.4.1, Para 1).</objectUse>
                  <objectValue valueForm="single" valueAllowed="000" valueTailoring="restrictable">Function, data for plans and description</objectValue>
                  <objectValue valueForm="single" valueAllowed="001" valueTailoring="restrictable">Title page</objectValue>
                  <objectValue valueForm="single" valueAllowed="002" valueTailoring="restrictable">List of pages or data modules See also code 00R and code 00S</objectValue>
                  <objectValue valueForm="single" valueAllowed="003" valueTailoring="restrictable">Change record or highlights See also code 00T and code 00U</objectValue>
                  <objectValue valueForm="single" valueAllowed="004" valueTailoring="restrictable">Access illustration</objectValue>
                  <objectValue valueForm="single" valueAllowed="005" valueTailoring="restrictable">List of abbreviations</objectValue>
                  <objectValue valueForm="single" valueAllowed="006" valueTailoring="restrictable">List of terms</objectValue>
                  <objectValue valueForm="single" valueAllowed="007" valueTailoring="restrictable">List of symbols</objectValue>
                  <objectValue valueForm="single" valueAllowed="008" valueTailoring="restrictable">Technical standard record</objectValue>
                  <objectValue valueForm="single" valueAllowed="009" valueTailoring="restrictable">Table of contents</objectValue>
                  <objectValue valueForm="single" valueAllowed="010" valueTailoring="restrictable">General data</objectValue>
                  <objectValue valueForm="single" valueAllowed="011" valueTailoring="restrictable">Function</objectValue>
                  <objectValue valueForm="single" valueAllowed="012" valueTailoring="restrictable">General warnings and cautions and related safety data</objectValue>
                  <objectValue valueForm="single" valueAllowed="013" valueTailoring="restrictable">Numeric index</objectValue>
                  <objectValue valueForm="single" valueAllowed="014" valueTailoring="restrictable">Alphabetic and alphanumeric index</objectValue>
                  <objectValue valueForm="single" valueAllowed="015" valueTailoring="restrictable">List of special materials</objectValue>
                  <objectValue valueForm="single" valueAllowed="016" valueTailoring="restrictable">List of dangerous materials</objectValue>
                  <objectValue valueForm="single" valueAllowed="017" valueTailoring="restrictable">List of related data - refer to code 00V</objectValue>
                  <objectValue valueForm="single" valueAllowed="018" valueTailoring="restrictable">Introduction</objectValue>
                  <objectValue valueForm="single" valueAllowed="019" valueTailoring="restrictable">Supplier list</objectValue>
                  <objectValue valueForm="single" valueAllowed="020" valueTailoring="restrictable">Configuration</objectValue>
                  <objectValue valueForm="single" valueAllowed="021" valueTailoring="restrictable">Copyright</objectValue>
                  <objectValue valueForm="single" valueAllowed="022" valueTailoring="restrictable" reasonForUpdateRefIds="CPF2013-072US" changeType="modify" changeMark="1">Business rules exchange</objectValue>
                  <objectValue valueForm="single" valueAllowed="023" valueTailoring="restrictable">Administrative forms and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="024" valueTailoring="restrictable" reasonForUpdateRefIds="CPF2013-072US" changeType="modify" changeMark="1">Business rules</objectValue>
                  <objectValue valueForm="single" valueAllowed="025" valueTailoring="restrictable" reasonForUpdateRefIds="CPF2017-014AA" changeType="add" changeMark="1">Export control policy</objectValue>
                  <objectValue valueForm="single" valueAllowed="026" valueTailoring="restrictable" reasonForUpdateRefIds="CPF2017-014AA" changeType="add" changeMark="1">Regulatory policy</objectValue>
                  <!-- Value 027 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="028" valueTailoring="restrictable">General Refer to codes 010 and 018</objectValue>
                  <objectValue valueForm="single" valueAllowed="029" valueTailoring="restrictable">Data structure</objectValue>
                  <objectValue valueForm="single" valueAllowed="030" valueTailoring="restrictable">Technical data</objectValue>
                  <objectValue valueForm="single" valueAllowed="031" valueTailoring="restrictable">Electrical standard parts data</objectValue>
                  <!-- Value 032 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="033" valueTailoring="restrictable">Technical data (functional breakdown)</objectValue>
                  <objectValue valueForm="single" valueAllowed="034" valueTailoring="restrictable">Technical data (physical breakdown)</objectValue>
                  <!-- Value 035 not available for projects -->
                  <!-- Value 036 not available for projects -->
                  <!-- Value 037 not available for projects -->
                  <!-- Value 038 not available for projects -->
                  <!-- Value 039 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="040" valueTailoring="restrictable">Description</objectValue>
                  <objectValue valueForm="single" valueAllowed="041" valueTailoring="restrictable">Description of how it is made</objectValue>
                  <objectValue valueForm="single" valueAllowed="042" valueTailoring="restrictable">Description of function</objectValue>
                  <objectValue valueForm="single" valueAllowed="043" valueTailoring="restrictable">Description of function attributed to crew (functional breakdown)</objectValue>
                  <objectValue valueForm="single" valueAllowed="044" valueTailoring="restrictable">Description of function (physical breakdown)</objectValue>
                  <objectValue valueForm="single" valueAllowed="045" valueTailoring="restrictable">Designated use</objectValue>
                  <objectValue valueForm="single" valueAllowed="046" valueTailoring="restrictable">Dependence on peripheral systems/equipment</objectValue>
                  <!-- Value 047 not available for projects -->
                  <!-- Value 048 not available for projects -->
                  <!-- Value 049 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="050" valueTailoring="restrictable">Diagram/List</objectValue>
                  <objectValue valueForm="single" valueAllowed="051" valueTailoring="restrictable">Wiring diagram</objectValue>
                  <objectValue valueForm="single" valueAllowed="052" valueTailoring="restrictable">Routing diagram</objectValue>
                  <objectValue valueForm="single" valueAllowed="053" valueTailoring="restrictable">Connection list</objectValue>
                  <objectValue valueForm="single" valueAllowed="054" valueTailoring="restrictable">Schematic diagram</objectValue>
                  <objectValue valueForm="single" valueAllowed="055" valueTailoring="restrictable">Location diagram</objectValue>
                  <objectValue valueForm="single" valueAllowed="056" valueTailoring="restrictable">Equipment list</objectValue>
                  <objectValue valueForm="single" valueAllowed="057" valueTailoring="restrictable">Wire list</objectValue>
                  <objectValue valueForm="single" valueAllowed="058" valueTailoring="restrictable">Harness list</objectValue>
                  <objectValue valueForm="single" valueAllowed="059" valueTailoring="restrictable">Maintenance envelope diagram</objectValue>
                  <objectValue valueForm="single" valueAllowed="060" valueTailoring="restrictable">Product support equipment, tools and software</objectValue>
                  <objectValue valueForm="single" valueAllowed="061" valueTailoring="restrictable">Special support equipment and tools</objectValue>
                  <objectValue valueForm="single" valueAllowed="062" valueTailoring="restrictable">Standard support equipment and tools</objectValue>
                  <objectValue valueForm="single" valueAllowed="063" valueTailoring="restrictable">Government supplied support equipment and tools</objectValue>
                  <objectValue valueForm="single" valueAllowed="064" valueTailoring="restrictable">Locally made support equipment and tools</objectValue>
                  <objectValue valueForm="single" valueAllowed="065" valueTailoring="restrictable">Software</objectValue>
                  <objectValue valueForm="single" valueAllowed="066" valueTailoring="restrictable">Support equipment and tools data</objectValue>
                  <objectValue valueForm="single" valueAllowed="067" valueTailoring="restrictable">Decals and instruction plates</objectValue>
                  <!-- Value 068 not available for projects -->
                  <!-- Value 069 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="070" valueTailoring="restrictable">Consumables, materials and expendables Supplies =consumables, materials and expendables</objectValue>
                  <objectValue valueForm="single" valueAllowed="071" valueTailoring="restrictable">Consumables</objectValue>
                  <objectValue valueForm="single" valueAllowed="072" valueTailoring="restrictable">Materials</objectValue>
                  <objectValue valueForm="single" valueAllowed="073" valueTailoring="restrictable">Expendables</objectValue>
                  <objectValue valueForm="single" valueAllowed="074" valueTailoring="restrictable">Data sheet for dangerous consumables and materials</objectValue>
                  <objectValue valueForm="single" valueAllowed="075" valueTailoring="restrictable">Parts list</objectValue>
                  <objectValue valueForm="single" valueAllowed="076" valueTailoring="restrictable">Fluid</objectValue>
                  <objectValue valueForm="single" valueAllowed="077" valueTailoring="restrictable">Data sheet for consumables and materials</objectValue>
                  <objectValue valueForm="single" valueAllowed="078" valueTailoring="restrictable">Fasteners</objectValue>
                  <!-- Value 079 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="080" valueTailoring="restrictable">Mixture and solution</objectValue>
                  <objectValue valueForm="single" valueAllowed="081" valueTailoring="restrictable">Chemical solution</objectValue>
                  <objectValue valueForm="single" valueAllowed="082" valueTailoring="restrictable">Chemical mixture</objectValue>
                  <!-- Value 083 not available for projects -->
                  <!-- Value 084 not available for projects -->
                  <!-- Value 085 not available for projects -->
                  <!-- Value 086 not available for projects -->
                  <!-- Value 087 not available for projects -->
                  <!-- Value 088 not available for projects -->
                  <!-- Value 089 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="090" valueTailoring="restrictable">Software documentation</objectValue>
                  <!-- Value 091 not available for projects -->
                  <!-- Value 092 not available for projects -->
                  <!-- Value 093 not available for projects -->
                  <!-- Value 094 not available for projects -->
                  <!-- Value 095 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="096" valueTailoring="restrictable">Safety items and parts</objectValue>
                  <!-- Value 097 not available for projects -->
                  <!-- Value 098 not available for projects -->
                  <!-- Value 099 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="00A" valueTailoring="restrictable">List of illustrations - normally used in front matter data modules</objectValue>
                  <objectValue valueForm="single" valueAllowed="00B" valueTailoring="restrictable">List of support equipment - ormally used in front matter data modules</objectValue>
                  <objectValue valueForm="single" valueAllowed="00C" valueTailoring="restrictable">List of supplies - normally used in front matter data modules</objectValue>
                  <objectValue valueForm="single" valueAllowed="00D" valueTailoring="restrictable">List of spares - normally used in front matter data modules</objectValue>
                  <objectValue valueForm="single" valueAllowed="00E" valueTailoring="restrictable">Functional item numbers common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00F" valueTailoring="restrictable">Circuit breakers common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00G" valueTailoring="restrictable">Parts common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00H" valueTailoring="restrictable">Zones common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00J" valueTailoring="restrictable">Access panels and doors common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00K" valueTailoring="restrictable">Organizations common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00L" valueTailoring="restrictable">Supplies - List of products common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00M" valueTailoring="restrictable">Supplies - List of requirements common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00N" valueTailoring="restrictable">Support equipment common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00P" valueTailoring="restrictable">Product Cross-reference Table (PCT)</objectValue>
                  <objectValue valueForm="single" valueAllowed="00Q" valueTailoring="restrictable">Conditions Cross-reference Table (CCT)</objectValue>
                  <objectValue valueForm="single" valueAllowed="00R" valueTailoring="restrictable">List of effective pages - refer to code 002</objectValue>
                  <objectValue valueForm="single" valueAllowed="00S" valueTailoring="restrictable">List of effective data modules - refer to code 002</objectValue>
                  <objectValue valueForm="single" valueAllowed="00T" valueTailoring="restrictable">Change record - refer to code 003</objectValue>
                  <objectValue valueForm="single" valueAllowed="00U" valueTailoring="restrictable">Highlights - refer to code 003</objectValue>
                  <objectValue valueForm="single" valueAllowed="00V" valueTailoring="restrictable">List of applicable specifications and documentation - refer to code 017</objectValue>
                  <objectValue valueForm="single" valueAllowed="00W" valueTailoring="restrictable">Applicability Cross-reference Table (ACT)</objectValue>
                  <objectValue valueForm="single" valueAllowed="00X" valueTailoring="restrictable">Controls and indicators common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="00Y" valueTailoring="restrictable">List of charts and forms</objectValue>
                  <objectValue valueForm="single" valueAllowed="00Z" valueTailoring="restrictable">List of tables</objectValue>
                  <objectValue valueForm="single" valueAllowed="0A1" valueTailoring="restrictable">Functional and/or physical areas common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="0A2" valueTailoring="restrictable">Applicability repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="0A3" valueTailoring="restrictable">Applicability Cross-reference Table catalog</objectValue>
                  <objectValue valueForm="single" valueAllowed="0A4" valueTailoring="restrictable" >Warnings - List of warnings in the common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="0A5" valueTailoring="restrictable" >Cautions - List of cautions in the common information repository</objectValue>
                  <objectValue valueForm="single" valueAllowed="0B0" valueTailoring="restrictable">Maintenance planning information</objectValue>
                  <objectValue valueForm="single" valueAllowed="0B1" valueTailoring="restrictable">Time limits</objectValue>
                  <objectValue valueForm="single" valueAllowed="0B2" valueTailoring="restrictable">System maintenance/Inspection tasks list</objectValue>
                  <objectValue valueForm="single" valueAllowed="0B3" valueTailoring="restrictable">Structure maintenance/inspection tasks lists</objectValue>
                  <objectValue valueForm="single" valueAllowed="0B4" valueTailoring="restrictable">Zonal maintenance/inspection tasks list</objectValue>
                  <objectValue valueForm="single" valueAllowed="0B5" valueTailoring="restrictable">Unscheduled check</objectValue>
                  <!-- Values 0A6~0A9 and 0B6~0B9 not available for projects -->
                  <!-- With the exception of codes “00A” thru “00Z” projects can allocate alpha characters to the tertiary code 
                        only if the primary and secondary codes are already defined in Chap 8.4.1 and Chap 8.4.2. -->
                  <objectValue valueForm="pattern" valueAllowed="[0][1-9A-B][A-Z]" >Project available</objectValue>
                  <objectValue valueForm="single" valueAllowed="100" valueTailoring="restrictable">Operation</objectValue>
                  <objectValue valueForm="single" valueAllowed="101" valueTailoring="restrictable">List of consumables associated with operation</objectValue>
                  <objectValue valueForm="single" valueAllowed="102" valueTailoring="restrictable">List of materials associated with operation</objectValue>
                  <objectValue valueForm="single" valueAllowed="103" valueTailoring="restrictable">List of expendables associated with operation</objectValue>
                  <objectValue valueForm="single" valueAllowed="104" valueTailoring="restrictable">List of special support equipment and tools associated with operation</objectValue>
                  <objectValue valueForm="single" valueAllowed="105" valueTailoring="restrictable">List of support equipment and tools associated with operation</objectValue>
                  <objectValue valueForm="single" valueAllowed="106" valueTailoring="restrictable">List of software associated with operation</objectValue>
                  <objectValue valueForm="single" valueAllowed="107" valueTailoring="restrictable">Parts list associated with operation</objectValue>
                  <!-- Value 108 not available for projects -->
                  <!-- Value 109 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="110" valueTailoring="restrictable">Controls and indicators</objectValue>
                  <objectValue valueForm="single" valueAllowed="111" valueTailoring="restrictable">Controls and indicators This code is used for crew</objectValue>
                  <objectValue valueForm="single" valueAllowed="112" valueTailoring="restrictable">Modes of operation This code is used for crew</objectValue>
                  <!-- Value 113 not available for projects -->
                  <!-- Value 114 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="115" valueTailoring="restrictable">Displays and alerts</objectValue>
                  <!-- Value 116 not available for projects -->
                  <!-- Value 117 not available for projects -->
                  <!-- Value 118 not available for projects -->
                  <!-- Value 119 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="120" valueTailoring="restrictable">Pre-operation</objectValue>
                  <objectValue valueForm="single" valueAllowed="121" valueTailoring="restrictable">Pre-operation procedure This code is used for crew</objectValue>
                  <objectValue valueForm="single" valueAllowed="122" valueTailoring="restrictable">Siting</objectValue>
                  <objectValue valueForm="single" valueAllowed="123" valueTailoring="restrictable">Shelter</objectValue>
                  <!-- Value 124 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="125" valueTailoring="restrictable">Pre-operation procedures checklist - this code is used for crew</objectValue>
                  <objectValue valueForm="single" valueAllowed="126" valueTailoring="restrictable">Conditions of readiness</objectValue>
                  <objectValue valueForm="single" valueAllowed="127" valueTailoring="restrictable">Establish operating position</objectValue>
                  <!-- Value 128 not available for projects -->
                  <!-- Value 129 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="130" valueTailoring="restrictable">Normal operation</objectValue>
                  <objectValue valueForm="single" valueAllowed="131" valueTailoring="restrictable">Normal operation procedure This code is used for crew</objectValue>
                  <objectValue valueForm="single" valueAllowed="132" valueTailoring="restrictable">Start-up procedure for maintenance</objectValue>
                  <objectValue valueForm="single" valueAllowed="133" valueTailoring="restrictable">Shutdown procedure for maintenance</objectValue>
                  <objectValue valueForm="single" valueAllowed="134" valueTailoring="restrictable">Aviation checklist</objectValue>
                  <objectValue valueForm="single" valueAllowed="135" valueTailoring="restrictable">Normal operation procedures checklist This code is used for crew</objectValue>
                  <objectValue valueForm="single" valueAllowed="136" valueTailoring="restrictable">Ground running check</objectValue>
                  <!-- Value 137 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="138" valueTailoring="restrictable">Ground running and performance adjustment</objectValue>
                  <objectValue valueForm="single" valueAllowed="139" valueTailoring="restrictable">Nuclear, biological and chemical procedures</objectValue>
                  <objectValue valueForm="single" valueAllowed="140" valueTailoring="restrictable">Emergency procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="141" valueTailoring="restrictable">Emergency operation procedure This code is used for crew</objectValue>
                  <objectValue valueForm="single" valueAllowed="142" valueTailoring="restrictable">Operation under unusual conditions</objectValue>
                  <objectValue valueForm="single" valueAllowed="143" valueTailoring="restrictable">Radio interference suppression</objectValue>
                  <objectValue valueForm="single" valueAllowed="144" valueTailoring="restrictable">Jamming and electronic countermeasures (ECM)</objectValue>
                  <objectValue valueForm="single" valueAllowed="145" valueTailoring="restrictable">Emergency operation procedures checklist This code is used for crew</objectValue>
                  <objectValue valueForm="single" valueAllowed="146" valueTailoring="restrictable">Emergency shutdown operation procedure (Checklist)</objectValue>
                  <!-- Value 147 not available for projects -->
                  <!-- Value 148 not available for projects -->
                  <!-- Value 149 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="150" valueTailoring="restrictable">Post-operation</objectValue>
                  <objectValue valueForm="single" valueAllowed="151" valueTailoring="restrictable">Post-operation procedure This code is used for crew</objectValue>
                  <!-- Value 152 not available for projects -->
                  <!-- Value 153 not available for projects -->
                  <!-- Value 154 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="155" valueTailoring="restrictable">Post-operation procedures checklist This code is used for crew</objectValue>
                  <!-- Value 156 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="157" valueTailoring="restrictable">Establish maintenance position</objectValue>
                  <!-- Value 158 not available for projects -->
                  <!-- Value 159 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="160" valueTailoring="restrictable">Loading/Unloading procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="161" valueTailoring="restrictable">Special operation</objectValue>
                  <objectValue valueForm="single" valueAllowed="162" valueTailoring="restrictable">Non-tactical operation</objectValue>
                  <!-- Value 163 not available for projects -->
                  <!-- Value 164 not available for projects -->
                  <!-- Value 165 not available for projects -->
                  <!-- Value 166 not available for projects -->
                  <!-- Value 167 not available for projects -->
                  <!-- Value 168 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="169" valueTailoring="restrictable" reasonForUpdateRefIds="EPWG2" changeType="modify" changeMark="0">Mass and balance</objectValue>
                  <objectValue valueForm="single" valueAllowed="170" valueTailoring="restrictable">Handling</objectValue>
                  <objectValue valueForm="single" valueAllowed="171" valueTailoring="restrictable">Lifting</objectValue>
                  <objectValue valueForm="single" valueAllowed="172" valueTailoring="restrictable">Jacking</objectValue>
                  <objectValue valueForm="single" valueAllowed="173" valueTailoring="restrictable">Shoring</objectValue>
                  <objectValue valueForm="single" valueAllowed="174" valueTailoring="restrictable">Towing</objectValue>
                  <objectValue valueForm="single" valueAllowed="175" valueTailoring="restrictable">Taxiing</objectValue>
                  <objectValue valueForm="single" valueAllowed="176" valueTailoring="restrictable">Lowering</objectValue>
                  <objectValue valueForm="single" valueAllowed="177" valueTailoring="restrictable">Stabilizing</objectValue>
                  <objectValue valueForm="single" valueAllowed="178" valueTailoring="restrictable">Tethering</objectValue>
                  <objectValue valueForm="single" valueAllowed="179" valueTailoring="restrictable">Debogging</objectValue>
                  <!-- Values 17A~17Z available for projects -->
                  <objectValue valueForm="single" valueAllowed="180" valueTailoring="restrictable">Dispatch deviation</objectValue>
                  <objectValue valueForm="single" valueAllowed="181" valueTailoring="restrictable">Deactivate for dispatch deviation</objectValue>
                  <!-- Value 182 not available for projects -->
                  <!-- Value 183 not available for projects -->
                  <!-- Value 184 not available for projects -->
                  <!-- Value 185 not available for projects -->
                  <!-- Value 186 not available for projects -->
                  <!-- Value 187 not available for projects -->
                  <!-- Value 188 not available for projects -->
                  <!-- Value 189 not available for projects -->
                  <!-- Values YYA through YYZ are available for projects when YY is S1000D allocated -->
                  <!-- Values 190~199 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[1][0-8][A-Z]">Project available</objectValue>
                  <objectValue valueForm="single" valueAllowed="200" valueTailoring="restrictable">Servicing</objectValue>
                  <objectValue valueForm="single" valueAllowed="201" valueTailoring="restrictable">List of consumables associated with servicing</objectValue>
                  <objectValue valueForm="single" valueAllowed="202" valueTailoring="restrictable">List of materials associated with servicing</objectValue>
                  <objectValue valueForm="single" valueAllowed="203" valueTailoring="restrictable">List of expendables associated with servicing</objectValue>
                  <objectValue valueForm="single" valueAllowed="204" valueTailoring="restrictable">List of special support equipment and tools associated with servicing</objectValue>
                  <objectValue valueForm="single" valueAllowed="205" valueTailoring="restrictable">List of support equipment and tools associated with servicing</objectValue>
                  <objectValue valueForm="single" valueAllowed="206" valueTailoring="restrictable">List of software associated with servicing</objectValue>
                  <objectValue valueForm="single" valueAllowed="207" valueTailoring="restrictable">Parts list associated with servicing</objectValue>
                  <!-- Value 208 not available for projects -->
                  <!-- Value 209 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="210" valueTailoring="restrictable">Fill</objectValue>
                  <objectValue valueForm="single" valueAllowed="211" valueTailoring="restrictable">Refuel</objectValue>
                  <objectValue valueForm="single" valueAllowed="212" valueTailoring="restrictable">Fill with oil</objectValue>
                  <objectValue valueForm="single" valueAllowed="213" valueTailoring="restrictable">Fill with oxygen</objectValue>
                  <objectValue valueForm="single" valueAllowed="214" valueTailoring="restrictable">Fill with nitrogen</objectValue>
                  <objectValue valueForm="single" valueAllowed="215" valueTailoring="restrictable">Fill with air</objectValue>
                  <objectValue valueForm="single" valueAllowed="216" valueTailoring="restrictable">Fill with water</objectValue>
                  <objectValue valueForm="single" valueAllowed="217" valueTailoring="restrictable">Fill with hydrogen</objectValue>
                  <objectValue valueForm="single" valueAllowed="218" valueTailoring="restrictable">Fill with other liquid</objectValue>
                  <objectValue valueForm="single" valueAllowed="219" valueTailoring="restrictable">Fill with other gas</objectValue>
                  <objectValue valueForm="single" valueAllowed="220" valueTailoring="restrictable">Drain liquid and release pressure</objectValue>
                  <objectValue valueForm="single" valueAllowed="221" valueTailoring="restrictable">Defuel and drain fuel</objectValue>
                  <objectValue valueForm="single" valueAllowed="222" valueTailoring="restrictable">Drain oil</objectValue>
                  <objectValue valueForm="single" valueAllowed="223" valueTailoring="restrictable">Release oxygen pressure</objectValue>
                  <objectValue valueForm="single" valueAllowed="224" valueTailoring="restrictable">Release nitrogen pressure</objectValue>
                  <objectValue valueForm="single" valueAllowed="225" valueTailoring="restrictable">Release air pressure</objectValue>
                  <objectValue valueForm="single" valueAllowed="226" valueTailoring="restrictable">Drain water</objectValue>
                  <objectValue valueForm="single" valueAllowed="227" valueTailoring="restrictable">Release hydrogen pressure</objectValue>
                  <objectValue valueForm="single" valueAllowed="228" valueTailoring="restrictable">Drain other liquid</objectValue>
                  <objectValue valueForm="single" valueAllowed="229" valueTailoring="restrictable">Release other gas pressure</objectValue>
                  <objectValue valueForm="single" valueAllowed="230" valueTailoring="restrictable">Bleed and prime</objectValue>
                  <objectValue valueForm="single" valueAllowed="231" valueTailoring="restrictable">Bleed</objectValue>
                  <objectValue valueForm="single" valueAllowed="232" valueTailoring="restrictable">Prime</objectValue>
                  <objectValue valueForm="single" valueAllowed="233" valueTailoring="restrictable">Dry</objectValue>
                  <objectValue valueForm="single" valueAllowed="234" valueTailoring="restrictable">Facility requirements associated with servicing</objectValue>
                  <objectValue valueForm="single" valueAllowed="235" valueTailoring="restrictable" reasonForUpdateRefIds="CPF2018-002US" changeType="add" changeMark="1">Flush</objectValue>
                  <objectValue valueForm="single" valueAllowed="236" valueTailoring="restrictable">Fill with inert gas/inert liquid</objectValue>
                  <objectValue valueForm="single" valueAllowed="237" valueTailoring="restrictable">Evacuate</objectValue>
                  <!-- Value 238 not available for projects -->
                  <!-- Value 239 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="240" valueTailoring="restrictable">Lubrication</objectValue>
                  <objectValue valueForm="single" valueAllowed="241" valueTailoring="restrictable">Oil</objectValue>
                  <objectValue valueForm="single" valueAllowed="242" valueTailoring="restrictable">Grease</objectValue>
                  <objectValue valueForm="single" valueAllowed="243" valueTailoring="restrictable">Dry film</objectValue>
                  <!-- Value 244 not available for projects -->
                  <!-- Value 245 not available for projects -->
                  <!-- Value 246 not available for projects -->
                  <!-- Value 247 not available for projects -->
                  <!-- Value 248 not available for projects -->
                  <!-- Value 249 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="250" valueTailoring="restrictable">Clean and apply surface protection</objectValue>
                  <objectValue valueForm="single" valueAllowed="251" valueTailoring="restrictable">Clean with chemical agent</objectValue>
                  <objectValue valueForm="single" valueAllowed="252" valueTailoring="restrictable">Clean by abrasive blast</objectValue>
                  <objectValue valueForm="single" valueAllowed="253" valueTailoring="restrictable">Clean by ultrasonics</objectValue>
                  <objectValue valueForm="single" valueAllowed="254" valueTailoring="restrictable">Clean mechanically</objectValue>
                  <objectValue valueForm="single" valueAllowed="255" valueTailoring="restrictable">Purge</objectValue>
                  <objectValue valueForm="single" valueAllowed="256" valueTailoring="restrictable">Polish and apply wax</objectValue>
                  <objectValue valueForm="single" valueAllowed="257" valueTailoring="restrictable">Paint and apply marking</objectValue>
                  <objectValue valueForm="single" valueAllowed="258" valueTailoring="restrictable">Other procedure to clean</objectValue>
                  <objectValue valueForm="single" valueAllowed="259" valueTailoring="restrictable">Other procedure to protect surfaces</objectValue>
                  <objectValue valueForm="single" valueAllowed="260" valueTailoring="restrictable">Remove and prevent ice and remove contamination</objectValue>
                  <objectValue valueForm="single" valueAllowed="261" valueTailoring="restrictable">Remove ice</objectValue>
                  <objectValue valueForm="single" valueAllowed="262" valueTailoring="restrictable">Prevent ice</objectValue>
                  <objectValue valueForm="single" valueAllowed="263" valueTailoring="restrictable">Use disinfectant/Sanitize</objectValue>
                  <objectValue valueForm="single" valueAllowed="264" valueTailoring="restrictable">Remove contamination</objectValue>
                  <!-- Value 265 not available for projects -->
                  <!-- Value 266 not available for projects -->
                  <!-- Value 267 not available for projects -->
                  <!-- Value 268 not available for projects -->
                  <!-- Value 269 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="270" valueTailoring="restrictable">Adjust, align and calibrate</objectValue>
                  <objectValue valueForm="single" valueAllowed="271" valueTailoring="restrictable">Adjust</objectValue>
                  <objectValue valueForm="single" valueAllowed="272" valueTailoring="restrictable">Align</objectValue>
                  <objectValue valueForm="single" valueAllowed="273" valueTailoring="restrictable">Calibrate</objectValue>
                  <objectValue valueForm="single" valueAllowed="274" valueTailoring="restrictable">Harmonize</objectValue>
                  <objectValue valueForm="single" valueAllowed="275" valueTailoring="restrictable">Grooming</objectValue>
                  <objectValue valueForm="single" valueAllowed="276" valueTailoring="restrictable">Rig</objectValue>
                  <objectValue valueForm="single" valueAllowed="277" valueTailoring="restrictable">Compensate</objectValue>
                  <objectValue valueForm="single" valueAllowed="278" valueTailoring="restrictable">Easily and quickly adjust after a battle damage repair</objectValue>
                  <objectValue valueForm="single" valueAllowed="279" valueTailoring="restrictable">Easily and quickly align after a battle damage repair</objectValue>
                  <objectValue valueForm="single" valueAllowed="280" valueTailoring="restrictable">Inspection</objectValue>
                  <objectValue valueForm="single" valueAllowed="281" valueTailoring="restrictable">Scheduled inspection</objectValue>
                  <objectValue valueForm="single" valueAllowed="282" valueTailoring="restrictable">Unscheduled inspection</objectValue>
                  <objectValue valueForm="single" valueAllowed="283" valueTailoring="restrictable">Special regular inspection</objectValue>
                  <objectValue valueForm="single" valueAllowed="284" valueTailoring="restrictable">Special irregular inspection</objectValue>
                  <objectValue valueForm="single" valueAllowed="285" valueTailoring="restrictable">Structure inspections for allowable damage limits</objectValue>
                  <objectValue valueForm="single" valueAllowed="286" valueTailoring="restrictable">Structure inspections for repair</objectValue>
                  <!-- Value 287 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="288" valueTailoring="restrictable">Overhaul and retirement schedule</objectValue>
                  <objectValue valueForm="single" valueAllowed="289" valueTailoring="restrictable">Check for filling quantity</objectValue>
                  <objectValue valueForm="single" valueAllowed="290" valueTailoring="restrictable">Change of liquid/gas</objectValue>
                  <!-- Value 291 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="292" valueTailoring="restrictable">Change of oil Code 222 + code 212</objectValue>
                  <objectValue valueForm="single" valueAllowed="293" valueTailoring="restrictable">Change of oxygen Code 223 + code 213</objectValue>
                  <objectValue valueForm="single" valueAllowed="294" valueTailoring="restrictable">Change of nitrogen Code 224 + code 214</objectValue>
                  <objectValue valueForm="single" valueAllowed="295" valueTailoring="restrictable">Change of air Code 225 + code 215</objectValue>
                  <objectValue valueForm="single" valueAllowed="296" valueTailoring="restrictable">Change of water Code 226 + code 216</objectValue>
                  <objectValue valueForm="single" valueAllowed="297" valueTailoring="restrictable">Change of hydrogen</objectValue>
                  <objectValue valueForm="single" valueAllowed="298" valueTailoring="restrictable">Change of other liquid Code 228 + code 218</objectValue>
                  <objectValue valueForm="single" valueAllowed="299" valueTailoring="restrictable">Change of other gas Code 229 + code 219</objectValue>
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[2][0-9][A-Z]">Project available</objectValue>
                  <objectValue valueForm="single" valueAllowed="300" valueTailoring="restrictable">Examinations, tests and checks</objectValue>
                  <objectValue valueForm="single" valueAllowed="301" valueTailoring="restrictable">List of consumables associated with examinations, tests and checks</objectValue>
                  <objectValue valueForm="single" valueAllowed="302" valueTailoring="restrictable">List of materials associated with examinations, tests and checks</objectValue>
                  <objectValue valueForm="single" valueAllowed="303" valueTailoring="restrictable">List of expendables associated with examinations, tests and checks</objectValue>
                  <objectValue valueForm="single" valueAllowed="304" valueTailoring="restrictable">List of special support equipment and tools associated with examinations, tests and checks</objectValue>
                  <objectValue valueForm="single" valueAllowed="305" valueTailoring="restrictable">List of support equipment and tools associated with examinations, tests and checks</objectValue>
                  <objectValue valueForm="single" valueAllowed="306" valueTailoring="restrictable">List of software associated with examinations, tests and checks</objectValue>
                  <objectValue valueForm="single" valueAllowed="307" valueTailoring="restrictable">Parts list associated with examinations, tests and checks</objectValue>
                  <!-- Value 308 not available for projects -->
                  <!-- Value 309 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="310" valueTailoring="restrictable">Visual examination</objectValue>
                  <objectValue valueForm="single" valueAllowed="311" valueTailoring="restrictable">Visual examination without special equipment</objectValue>
                  <objectValue valueForm="single" valueAllowed="312" valueTailoring="restrictable">Examination with a borescope</objectValue>
                  <!-- Value 312 not available for projects -->
                  <!-- Value 313 not available for projects -->
                  <!-- Value 314 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="315" valueTailoring="restrictable">QA requirements</objectValue>
                  <!-- Value 316 not available for projects -->
                  <!-- Value 317 not available for projects -->
                  <!-- Value 318 not available for projects -->
                  <!-- Value 319 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="320" valueTailoring="restrictable">Operation test</objectValue>
                  <objectValue valueForm="single" valueAllowed="321" valueTailoring="restrictable">Unit Break-in</objectValue>
                  <objectValue valueForm="single" valueAllowed="322" valueTailoring="restrictable">Test and inspection</objectValue>
                  <!-- Value 323 not available for projects -->
                  <!-- Value 324 not available for projects -->
                  <!-- Value 325 not available for projects -->
                  <!-- Value 326 not available for projects -->
                  <!-- Value 327 not available for projects -->
                  <!-- Value 328 not available for projects -->
                  <!-- Value 329 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="330" valueTailoring="restrictable">Test preparation</objectValue>
                  <objectValue valueForm="single" valueAllowed="331" valueTailoring="restrictable">Connection of test equipment</objectValue>
                  <objectValue valueForm="single" valueAllowed="332" valueTailoring="restrictable">Removal of test equipment</objectValue>
                  <objectValue valueForm="single" valueAllowed="333" valueTailoring="restrictable">Installation of the unit before the test</objectValue>
                  <objectValue valueForm="single" valueAllowed="334" valueTailoring="restrictable">Removal of the unit after the test</objectValue>
                  <objectValue valueForm="single" valueAllowed="335" valueTailoring="restrictable">Concluding Final measures</objectValue>
                  <!-- Value 336 not available for projects -->
                  <!-- Value 337 not available for projects -->
                  <!-- Value 338 not available for projects -->
                  <!-- Value 339 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="340" valueTailoring="restrictable">Function test</objectValue>
                  <objectValue valueForm="single" valueAllowed="341" valueTailoring="restrictable">Manual test</objectValue>
                  <objectValue valueForm="single" valueAllowed="342" valueTailoring="restrictable">Automatic test</objectValue>
                  <objectValue valueForm="single" valueAllowed="343" valueTailoring="restrictable">BIT</objectValue>
                  <objectValue valueForm="single" valueAllowed="344" valueTailoring="restrictable">Compatibility test</objectValue>
                  <objectValue valueForm="single" valueAllowed="345" valueTailoring="restrictable">System test</objectValue>
                  <objectValue valueForm="single" valueAllowed="346" valueTailoring="restrictable">Other check</objectValue>
                  <objectValue valueForm="single" valueAllowed="347" valueTailoring="restrictable">Start-up procedure for test</objectValue>
                  <objectValue valueForm="single" valueAllowed="348" valueTailoring="restrictable">Finial acceptance test (FAT)</objectValue>
                  <objectValue valueForm="single" valueAllowed="349" valueTailoring="restrictable">Test results</objectValue>
                  <objectValue valueForm="single" valueAllowed="350" valueTailoring="restrictable">Structure test</objectValue>
                  <objectValue valueForm="single" valueAllowed="351" valueTailoring="restrictable">Test for surface cracks with dye penetrant</objectValue>
                  <objectValue valueForm="single" valueAllowed="352" valueTailoring="restrictable">Test for surface cracks with magnetic particles</objectValue>
                  <objectValue valueForm="single" valueAllowed="353" valueTailoring="restrictable">Test for cracks and other defects with eddy current</objectValue>
                  <objectValue valueForm="single" valueAllowed="354" valueTailoring="restrictable">Test for cracks and other defects with X-rays</objectValue>
                  <objectValue valueForm="single" valueAllowed="355" valueTailoring="restrictable">Test for cracks and other defects with ultrasonic</objectValue>
                  <objectValue valueForm="single" valueAllowed="356" valueTailoring="restrictable">Hardness test</objectValue>
                  <objectValue valueForm="single" valueAllowed="357" valueTailoring="restrictable">Gamma ray</objectValue>
                  <objectValue valueForm="single" valueAllowed="358" valueTailoring="restrictable">Resonance frequency</objectValue>
                  <objectValue valueForm="single" valueAllowed="359" valueTailoring="restrictable">Thermographic test</objectValue>
                  <objectValue valueForm="single" valueAllowed="360" valueTailoring="restrictable">Design data/tolerances check</objectValue>
                  <objectValue valueForm="single" valueAllowed="361" valueTailoring="restrictable">Dimensions check</objectValue>
                  <objectValue valueForm="single" valueAllowed="362" valueTailoring="restrictable">Pressure check</objectValue>
                  <objectValue valueForm="single" valueAllowed="363" valueTailoring="restrictable">Flow check</objectValue>
                  <objectValue valueForm="single" valueAllowed="364" valueTailoring="restrictable">Leak check</objectValue>
                  <objectValue valueForm="single" valueAllowed="365" valueTailoring="restrictable">Continuity check</objectValue>
                  <objectValue valueForm="single" valueAllowed="366" valueTailoring="restrictable">Resistance check</objectValue>
                  <objectValue valueForm="single" valueAllowed="367" valueTailoring="restrictable">Electrical power check</objectValue>
                  <objectValue valueForm="single" valueAllowed="368" valueTailoring="restrictable">Signal strength check</objectValue>
                  <objectValue valueForm="single" valueAllowed="369" valueTailoring="restrictable">Other check</objectValue>
                  <objectValue valueForm="single" valueAllowed="370" valueTailoring="restrictable">Monitor the condition</objectValue>
                  <objectValue valueForm="single" valueAllowed="371" valueTailoring="restrictable">Oil analysis</objectValue>
                  <objectValue valueForm="single" valueAllowed="372" valueTailoring="restrictable">Vibration analysis</objectValue>
                  <objectValue valueForm="single" valueAllowed="373" valueTailoring="restrictable">Tracking check</objectValue>
                  <objectValue valueForm="single" valueAllowed="374" valueTailoring="restrictable">Fuel analysis</objectValue>
                  <objectValue valueForm="single" valueAllowed="375" valueTailoring="restrictable">Shooting accidental discharge analysis</objectValue>
                  <objectValue valueForm="single" valueAllowed="376" valueTailoring="restrictable">Check post application of adhesive</objectValue>
                  <objectValue valueForm="single" valueAllowed="377" valueTailoring="restrictable">Contamination analysis</objectValue>
                  <!-- Value 378 not available for projects -->
                  <!-- Value 379 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[3][0-7][A-Z]">Project available</objectValue>
                  <!-- Values 380~389 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="390" valueTailoring="restrictable">Sample test</objectValue>
                  <!-- Value 391 not available for projects -->
                  <!-- Value 392 not available for projects -->
                  <!-- Value 393 not available for projects -->
                  <!-- Value 394 not available for projects -->
                  <!-- Value 395 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="396" valueTailoring="restrictable">Flight control surface movement</objectValue>
                  <objectValue valueForm="single" valueAllowed="397" valueTailoring="restrictable">Landing gear movement</objectValue>
                  <objectValue valueForm="single" valueAllowed="398" valueTailoring="restrictable">Product configuration</objectValue>
                  <!-- Value 399 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[3][9][A-Z]">Project available</objectValue>
                  <objectValue valueForm="single" valueAllowed="400" valueTailoring="restrictable">Fault reports and isolation procedures</objectValue>
                  <objectValue valueForm="single" valueAllowed="401" valueTailoring="restrictable">List of consumables associated with fault diagnosis</objectValue>
                  <objectValue valueForm="single" valueAllowed="402" valueTailoring="restrictable">List of materials associated with fault diagnosis</objectValue>
                  <objectValue valueForm="single" valueAllowed="403" valueTailoring="restrictable">List of expendables associated with fault diagnosis</objectValue>
                  <objectValue valueForm="single" valueAllowed="404" valueTailoring="restrictable">List of special support equipment and tools associated with fault diagnosis</objectValue>
                  <objectValue valueForm="single" valueAllowed="405" valueTailoring="restrictable">List of support equipment and tools associated with fault diagnosis</objectValue>
                  <objectValue valueForm="single" valueAllowed="406" valueTailoring="restrictable">List of software associated with fault diagnosis</objectValue>
                  <objectValue valueForm="single" valueAllowed="407" valueTailoring="restrictable">Parts list associated with fault diagnosis</objectValue>
                  <!-- Value 408 not available for projects -->
                  <!-- Value 409 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="410" valueTailoring="restrictable">General fault description</objectValue>
                  <objectValue valueForm="single" valueAllowed="411" valueTailoring="restrictable">Isolated fault</objectValue>
                  <objectValue valueForm="single" valueAllowed="412" valueTailoring="restrictable">Detected fault</objectValue>
                  <objectValue valueForm="single" valueAllowed="413" valueTailoring="restrictable">Observed fault</objectValue>
                  <objectValue valueForm="single" valueAllowed="414" valueTailoring="restrictable">Correlated fault</objectValue>
                  <objectValue valueForm="single" valueAllowed="415" valueTailoring="restrictable">Impact of fault</objectValue>
                  <!-- Value 416 not available for projects -->
                  <!-- Value 417 not available for projects -->
                  <!-- Value 418 not available for projects -->
                  <!-- Value 419 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="420" valueTailoring="restrictable">General fault isolation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="421" valueTailoring="restrictable">Fault isolation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="422" valueTailoring="restrictable" >Fault isolation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="423" valueTailoring="restrictable" >Fault isolation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="424" valueTailoring="restrictable" >Fault isolation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="425" valueTailoring="restrictable" >Fault isolation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="426" valueTailoring="restrictable" >Fault isolation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="427" valueTailoring="restrictable" >Fault isolation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="428" valueTailoring="restrictable" >Fault isolation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="429" valueTailoring="restrictable">Diagnostics</objectValue>
                  <objectValue valueForm="single" valueAllowed="430" valueTailoring="restrictable">Fault isolation task supporting data</objectValue>
                  <!-- Values 431~439 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="440" valueTailoring="restrictable">Index</objectValue>
                  <objectValue valueForm="single" valueAllowed="441" valueTailoring="restrictable">Fault code index</objectValue>
                  <objectValue valueForm="single" valueAllowed="442" valueTailoring="restrictable">Maintenance message index</objectValue>
                  <objectValue valueForm="single" valueAllowed="443" valueTailoring="restrictable">Post-troubleshooting shutdown procedures</objectValue>
                  <!-- Value 444 not available for projects -->
                  <!-- Value 445 not available for projects -->
                  <!-- Value 446 not available for projects -->
                  <!-- Value 447 not available for projects -->
                  <!-- Value 448 not available for projects -->
                  <!-- Value 449 not available for projects -->
                  <!-- Value 450~499 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[4][0-4][A-Z]">Project available</objectValue>
                  <objectValue valueForm="single" valueAllowed="500" valueTailoring="restrictable">Disconnect, remove and disassemble procedures</objectValue>
                  <objectValue valueForm="single" valueAllowed="501" valueTailoring="restrictable">List of consumables associated with removal</objectValue>
                  <objectValue valueForm="single" valueAllowed="502" valueTailoring="restrictable">List of materials associated with removal</objectValue>
                  <objectValue valueForm="single" valueAllowed="503" valueTailoring="restrictable">List of expendables associated with removal</objectValue>
                  <objectValue valueForm="single" valueAllowed="504" valueTailoring="restrictable">List of special support equipment and tools associated with removal</objectValue>
                  <objectValue valueForm="single" valueAllowed="505" valueTailoring="restrictable">List of support equipment and tools associated with removal</objectValue>
                  <objectValue valueForm="single" valueAllowed="506" valueTailoring="restrictable">List of software associated with removal</objectValue>
                  <objectValue valueForm="single" valueAllowed="507" valueTailoring="restrictable">Parts list associated with removal</objectValue>
                  <!-- Value 508 not available for projects -->
                  <!-- Value 509 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="510" valueTailoring="restrictable">Disconnect procedure</objectValue>
                  <!-- Values 511~519 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="520" valueTailoring="restrictable">Remove procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="521" valueTailoring="restrictable">Return to basic configuration Undressing</objectValue>
                  <objectValue valueForm="single" valueAllowed="522" valueTailoring="restrictable">Remove support equipment/Remove from support equipment</objectValue>
                  <objectValue valueForm="single" valueAllowed="523" valueTailoring="restrictable">Preparation before removal</objectValue>
                  <objectValue valueForm="single" valueAllowed="524" valueTailoring="restrictable">Follow-on maintenance</objectValue>
                  <objectValue valueForm="single" valueAllowed="525" valueTailoring="restrictable">Ammunition unloading</objectValue>
                  <objectValue valueForm="single" valueAllowed="526" valueTailoring="restrictable">Deactivate launching device</objectValue>
                  <!-- Value 527 not available for projects -->
                  <!-- Value 528 not available for projects -->
                  <!-- Value 529 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="530" valueTailoring="restrictable">Disassemble procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="531" valueTailoring="restrictable">Disassemble procedure on operation site</objectValue>
                  <!-- Values 532~539 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="540" valueTailoring="restrictable">Open for access procedure</objectValue>
                  <!-- Values 541~549 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="550" valueTailoring="restrictable">Unload software procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="551" valueTailoring="restrictable">Fault monitoring storage readout (downloading)</objectValue>
                  <objectValue valueForm="single" valueAllowed="552" valueTailoring="restrictable">Data erasing</objectValue>
                  <objectValue valueForm="single" valueAllowed="553" valueTailoring="restrictable">Display, copy and print of data</objectValue>
                  <!-- Value 554 not available for projects -->
                  <!-- Value 555 not available for projects -->
                  <!-- Value 556 not available for projects -->
                  <!-- Value 557 not available for projects -->
                  <!-- Value 558 not available for projects -->
                  <!-- Value 559 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="560" valueTailoring="restrictable">Deactivation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="561" valueTailoring="restrictable">De-Energize electrical network</objectValue>
                  <objectValue valueForm="single" valueAllowed="562" valueTailoring="restrictable">Depressurize hydraulics</objectValue>
                  <objectValue valueForm="single" valueAllowed="563" valueTailoring="restrictable">Deactivation maintenance practice</objectValue>
                  <!-- Value 564 not available for projects -->
                  <!-- Value 565 not available for projects -->
                  <!-- Value 566 not available for projects -->
                  <!-- Value 567 not available for projects -->
                  <!-- Value 568 not available for projects -->
                  <!-- Value 569 not available for projects -->
                  <!-- Values 570~599 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[5][0-6][A-Z]">Project available</objectValue>
                  <objectValue valueForm="single" valueAllowed="600" valueTailoring="restrictable">Repairs and locally make procedures and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="601" valueTailoring="restrictable">List of consumables associated with repairs</objectValue>
                  <objectValue valueForm="single" valueAllowed="602" valueTailoring="restrictable">List of materials associated with repairs</objectValue>
                  <objectValue valueForm="single" valueAllowed="603" valueTailoring="restrictable">List of expendables associated with repairs</objectValue>
                  <objectValue valueForm="single" valueAllowed="604" valueTailoring="restrictable">List of special support equipment and tools associated with repairs</objectValue>
                  <objectValue valueForm="single" valueAllowed="605" valueTailoring="restrictable">List of support equipment and tools associated with repairs</objectValue>
                  <objectValue valueForm="single" valueAllowed="606" valueTailoring="restrictable">List of software associated with repairs</objectValue>
                  <objectValue valueForm="single" valueAllowed="607" valueTailoring="restrictable">Parts list associated with repairs</objectValue>
                  <!-- Value 608 not available for projects -->
                  <!-- Value 609 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="610" valueTailoring="restrictable">Add material</objectValue>
                  <objectValue valueForm="single" valueAllowed="611" valueTailoring="restrictable">Insulation</objectValue>
                  <objectValue valueForm="single" valueAllowed="612" valueTailoring="restrictable">Metalize</objectValue>
                  <objectValue valueForm="single" valueAllowed="613" valueTailoring="restrictable">Pot</objectValue>
                  <objectValue valueForm="single" valueAllowed="614" valueTailoring="restrictable">Remetal</objectValue>
                  <objectValue valueForm="single" valueAllowed="615" valueTailoring="restrictable">Retread</objectValue>
                  <!-- Value 616 not available for projects -->
                  <!-- Value 617 not available for projects -->
                  <!-- Value 618 not available for projects -->
                  <!-- Value 619 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="620" valueTailoring="restrictable">Attach material</objectValue>
                  <objectValue valueForm="single" valueAllowed="621" valueTailoring="restrictable">Bond</objectValue>
                  <objectValue valueForm="single" valueAllowed="622" valueTailoring="restrictable">Crimp</objectValue>
                  <objectValue valueForm="single" valueAllowed="623" valueTailoring="restrictable">Braze</objectValue>
                  <objectValue valueForm="single" valueAllowed="624" valueTailoring="restrictable">Rivet</objectValue>
                  <objectValue valueForm="single" valueAllowed="625" valueTailoring="restrictable">Solder</objectValue>
                  <objectValue valueForm="single" valueAllowed="626" valueTailoring="restrictable">Splice</objectValue>
                  <objectValue valueForm="single" valueAllowed="627" valueTailoring="restrictable">Weld</objectValue>
                  <!-- Value 628 not available for projects -->
                  <!-- Value 629 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="630" valueTailoring="restrictable">Change the mechanical strength/structure of material</objectValue>
                  <objectValue valueForm="single" valueAllowed="631" valueTailoring="restrictable">Anneal</objectValue>
                  <objectValue valueForm="single" valueAllowed="632" valueTailoring="restrictable">Case harden</objectValue>
                  <objectValue valueForm="single" valueAllowed="633" valueTailoring="restrictable">Cure</objectValue>
                  <objectValue valueForm="single" valueAllowed="634" valueTailoring="restrictable">Normalize</objectValue>
                  <objectValue valueForm="single" valueAllowed="635" valueTailoring="restrictable">Shot-peen</objectValue>
                  <objectValue valueForm="single" valueAllowed="636" valueTailoring="restrictable">Temper</objectValue>
                  <!-- Value 637 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="638" valueTailoring="restrictable">Other treatment</objectValue>
                  <objectValue valueForm="single" valueAllowed="639" valueTailoring="restrictable">Other process to change the mechanical strength/structure of material</objectValue>
                  <objectValue valueForm="single" valueAllowed="640" valueTailoring="restrictable">Change the surface finish of material</objectValue>
                  <objectValue valueForm="single" valueAllowed="641" valueTailoring="restrictable">Anodize</objectValue>
                  <objectValue valueForm="single" valueAllowed="642" valueTailoring="restrictable">Buff</objectValue>
                  <objectValue valueForm="single" valueAllowed="643" valueTailoring="restrictable">Burnish</objectValue>
                  <objectValue valueForm="single" valueAllowed="644" valueTailoring="restrictable">Chromate</objectValue>
                  <objectValue valueForm="single" valueAllowed="645" valueTailoring="restrictable">Hone</objectValue>
                  <objectValue valueForm="single" valueAllowed="646" valueTailoring="restrictable">Lap</objectValue>
                  <objectValue valueForm="single" valueAllowed="647" valueTailoring="restrictable">Plate</objectValue>
                  <objectValue valueForm="single" valueAllowed="648" valueTailoring="restrictable">Polish</objectValue>
                  <objectValue valueForm="single" valueAllowed="649" valueTailoring="restrictable">Clean-up of dents, cracks and scratches</objectValue>
                  <objectValue valueForm="single" valueAllowed="650" valueTailoring="restrictable">Remove material</objectValue>
                  <objectValue valueForm="single" valueAllowed="651" valueTailoring="restrictable">Abrasive blast</objectValue>
                  <objectValue valueForm="single" valueAllowed="652" valueTailoring="restrictable">Bore/drill/ream</objectValue>
                  <objectValue valueForm="single" valueAllowed="653" valueTailoring="restrictable">Electrical/electrochemical/chemical etch</objectValue>
                  <objectValue valueForm="single" valueAllowed="654" valueTailoring="restrictable">Broach</objectValue>
                  <objectValue valueForm="single" valueAllowed="655" valueTailoring="restrictable">Grind</objectValue>
                  <objectValue valueForm="single" valueAllowed="656" valueTailoring="restrictable">Mill</objectValue>
                  <objectValue valueForm="single" valueAllowed="657" valueTailoring="restrictable">Thread/tap</objectValue>
                  <objectValue valueForm="single" valueAllowed="658" valueTailoring="restrictable">Turn</objectValue>
                  <objectValue valueForm="single" valueAllowed="659" valueTailoring="restrictable">Other process to remove material</objectValue>
                  <objectValue valueForm="single" valueAllowed="660" valueTailoring="restrictable">Structure repair procedure and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="661" valueTailoring="restrictable">Allowable damage</objectValue>
                  <objectValue valueForm="single" valueAllowed="662" valueTailoring="restrictable">Temporary repair procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="663" valueTailoring="restrictable">Standard repair procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="664" valueTailoring="restrictable">Special repair procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="665" valueTailoring="restrictable">Fly-in repair procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="666" valueTailoring="restrictable">Material classification</objectValue>
                  <objectValue valueForm="single" valueAllowed="667" valueTailoring="restrictable">Structure classification</objectValue>
                  <objectValue valueForm="single" valueAllowed="668" valueTailoring="restrictable">Allowable damage of composite structures</objectValue>
                  <objectValue valueForm="single" valueAllowed="669" valueTailoring="restrictable">Allowable damage of mixed structures</objectValue>
                  <objectValue valueForm="single" valueAllowed="670" valueTailoring="restrictable">Locally make procedure and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="671" valueTailoring="restrictable">Making of parts</objectValue>
                  <!-- Value 672 not available for projects -->
                  <!-- Value 673 not available for projects -->
                  <!-- Value 674 not available for projects -->
                  <!-- Value 675 not available for projects -->
                  <!-- Value 676 not available for projects -->
                  <!-- Value 677 not available for projects -->
                  <!-- Value 678 not available for projects -->
                  <!-- Value 679 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="680" valueTailoring="restrictable">Battle damage repair procedure and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="681" valueTailoring="restrictable">Damage repair symbol marking</objectValue>
                  <objectValue valueForm="single" valueAllowed="682" valueTailoring="restrictable">Identification of damaged hardware</objectValue>
                  <objectValue valueForm="single" valueAllowed="683" valueTailoring="restrictable">Damage assessment</objectValue>
                  <objectValue valueForm="single" valueAllowed="684" valueTailoring="restrictable">Utilization degradation</objectValue>
                  <objectValue valueForm="single" valueAllowed="685" valueTailoring="restrictable">Repair procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="686" valueTailoring="restrictable">Isolation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="687" valueTailoring="restrictable">Function test after battle damage repair</objectValue>
                  <objectValue valueForm="single" valueAllowed="688" valueTailoring="restrictable">Battle damage repair kit</objectValue>
                  <objectValue valueForm="single" valueAllowed="689" valueTailoring="restrictable">Damage repair</objectValue>
                  <objectValue valueForm="single" valueAllowed="690" valueTailoring="restrictable">Miscellaneous</objectValue>
                  <objectValue valueForm="single" valueAllowed="691" valueTailoring="restrictable">Marking</objectValue>
                  <objectValue valueForm="single" valueAllowed="692" valueTailoring="restrictable">Connector repair</objectValue>
                  <objectValue valueForm="single" valueAllowed="693" valueTailoring="restrictable">Varnish</objectValue>
                  <!-- Value 694 not available for projects -->
                  <!-- Value 695 not available for projects -->
                  <!-- Value 696 not available for projects -->
                  <!-- Value 697 not available for projects -->
                  <!-- Value 698 not available for projects -->
                  <!-- Value 699 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[6][0-9][A-Z]">Project available</objectValue>
                  <objectValue valueForm="single" valueAllowed="700" valueTailoring="restrictable">Assemble, install and connect procedures</objectValue>
                  <objectValue valueForm="single" valueAllowed="701" valueTailoring="restrictable">List of consumables associated with installation</objectValue>
                  <objectValue valueForm="single" valueAllowed="702" valueTailoring="restrictable">List of materials associated with installation</objectValue>
                  <objectValue valueForm="single" valueAllowed="703" valueTailoring="restrictable">List of expendables associated with installation</objectValue>
                  <objectValue valueForm="single" valueAllowed="704" valueTailoring="restrictable">List of special support equipment and tools associated with installation</objectValue>
                  <objectValue valueForm="single" valueAllowed="705" valueTailoring="restrictable">List of support equipment and tools associated with installation</objectValue>
                  <objectValue valueForm="single" valueAllowed="706" valueTailoring="restrictable">List of software associated with installation</objectValue>
                  <objectValue valueForm="single" valueAllowed="707" valueTailoring="restrictable">Parts list associated with installation</objectValue>
                  <!-- Value 708 not available for projects -->
                  <!-- Value 709 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="710" valueTailoring="restrictable">Assemble procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="711" valueTailoring="restrictable">Tighten procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="712" valueTailoring="restrictable">Lock procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="713" valueTailoring="restrictable">Pack procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="714" valueTailoring="restrictable">Assemble procedure on operation site</objectValue>
                  <!-- Value 715 not available for projects -->
                  <!-- Value 716 not available for projects -->
                  <!-- Value 717 not available for projects -->
                  <!-- Value 718 not available for projects -->
                  <!-- Value 719 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="720" valueTailoring="restrictable">Install procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="721" valueTailoring="restrictable">Build up to usable configuration Dressing</objectValue>
                  <objectValue valueForm="single" valueAllowed="722" valueTailoring="restrictable">Install support equipment/Install on support equipment</objectValue>
                  <objectValue valueForm="single" valueAllowed="723" valueTailoring="restrictable">Preparation before installation</objectValue>
                  <objectValue valueForm="single" valueAllowed="724" valueTailoring="restrictable">Follow-on maintenance</objectValue>
                  <objectValue valueForm="single" valueAllowed="725" valueTailoring="restrictable">Ammunition loading</objectValue>
                  <objectValue valueForm="single" valueAllowed="726" valueTailoring="restrictable">Activate launching device</objectValue>
                  <objectValue valueForm="single" valueAllowed="727" valueTailoring="restrictable">Site location plan</objectValue>
                  <objectValue valueForm="single" valueAllowed="728" valueTailoring="restrictable">Foundation preparation</objectValue>
                  <!-- Value 729 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="730" valueTailoring="restrictable">Connect procedure</objectValue>
                  <!-- Values 731~739 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="740" valueTailoring="restrictable">Close after access procedure</objectValue>
                  <!-- Values 741~749 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="750" valueTailoring="restrictable">Load software procedure</objectValue>
                  <!-- Value 751 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="752" valueTailoring="restrictable">Data loading</objectValue>
                  <!-- Value 753 not available for projects -->
                  <!-- Value 754 not available for projects -->
                  <!-- Value 755 not available for projects -->
                  <!-- Value 756 not available for projects -->
                  <!-- Value 757 not available for projects -->
                  <!-- Value 758 not available for projects -->
                  <!-- Value 759 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="760" valueTailoring="restrictable">Reactivation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="761" valueTailoring="restrictable">Energize electrical network</objectValue>
                  <objectValue valueForm="single" valueAllowed="762" valueTailoring="restrictable">Pressurize hydraulics</objectValue>
                  <!-- Value 763 not available for projects -->
                  <!-- Value 764 not available for projects -->
                  <!-- Value 765 not available for projects -->
                  <!-- Value 766 not available for projects -->
                  <!-- Value 767 not available for projects -->
                  <!-- Value 768 not available for projects -->
                  <!-- Value 769 not available for projects -->
                  <!-- Values 770~799 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[7][0-6][A-Z]">Project available</objectValue>
                  <objectValue valueForm="single" valueAllowed="800" valueTailoring="restrictable">Package, handling, storage, and transportation</objectValue>
                  <objectValue valueForm="single" valueAllowed="801" valueTailoring="restrictable">List of consumables associated with storage</objectValue>
                  <objectValue valueForm="single" valueAllowed="802" valueTailoring="restrictable">List of materials associated with storage</objectValue>
                  <objectValue valueForm="single" valueAllowed="803" valueTailoring="restrictable">List of expendables associated with storage</objectValue>
                  <objectValue valueForm="single" valueAllowed="804" valueTailoring="restrictable">List of special support equipment and tools associated with storage</objectValue>
                  <objectValue valueForm="single" valueAllowed="805" valueTailoring="restrictable">List of support equipment and tools associated with storage</objectValue>
                  <objectValue valueForm="single" valueAllowed="806" valueTailoring="restrictable">List of software associated with storage</objectValue>
                  <objectValue valueForm="single" valueAllowed="807" valueTailoring="restrictable">Parts list associated with storage</objectValue>
                  <!-- Value 808 not available for projects -->
                  <!-- Value 809 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="810" valueTailoring="restrictable">Preservation procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="811" valueTailoring="restrictable">Preparation for vehicle transportation</objectValue>
                  <objectValue valueForm="single" valueAllowed="812" valueTailoring="restrictable">Shipping and storage - General</objectValue>
                  <!-- Value 813 not available for projects -->
                  <!-- Value 814 not available for projects -->
                  <!-- Value 815 not available for projects -->
                  <!-- Value 816 not available for projects -->
                  <!-- Value 817 not available for projects -->
                  <!-- Value 818 not available for projects -->
                  <!-- Value 819 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="820" valueTailoring="restrictable">Procedure to remove preservation material</objectValue>
                  <!-- Value 821 not available for projects -->
                  <!-- Value 822 not available for projects -->
                  <!-- Value 823 not available for projects -->
                  <!-- Value 824 not available for projects -->
                  <!-- Value 825 not available for projects -->
                  <!-- Value 826 not available for projects -->
                  <!-- Value 827 not available for projects -->
                  <!-- Value 828 not available for projects -->
                  <!-- Value 829 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="830" valueTailoring="restrictable">Procedure to put item in containers</objectValue>
                  <objectValue valueForm="single" valueAllowed="831" valueTailoring="restrictable">Vehicle loading</objectValue>
                  <objectValue valueForm="single" valueAllowed="832" valueTailoring="restrictable">Procedure to pack items</objectValue>
                  <!-- Value 833 not available for projects -->
                  <!-- Value 834 not available for projects -->
                  <!-- Value 835 not available for projects -->
                  <!-- Value 836 not available for projects -->
                  <!-- Value 837 not available for projects -->
                  <!-- Value 838 not available for projects -->
                  <!-- Value 839 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="840" valueTailoring="restrictable">Procedure to remove item from containers</objectValue>
                  <objectValue valueForm="single" valueAllowed="841" valueTailoring="restrictable">Vehicle unloading</objectValue>
                  <objectValue valueForm="single" valueAllowed="842" valueTailoring="restrictable">Procedure to unpack items</objectValue>
                  <!-- Value 843 not available for projects -->
                  <!-- Value 844 not available for projects -->
                  <!-- Value 845 not available for projects -->
                  <!-- Value 846 not available for projects -->
                  <!-- Value 847 not available for projects -->
                  <!-- Value 848 not available for projects -->
                  <!-- Value 849 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="850" valueTailoring="restrictable">Procedure to keep item serviceable when in storage</objectValue>
                  <!-- Value 851 not available for projects -->
                  <!-- Value 852 not available for projects -->
                  <!-- Value 853 not available for projects -->
                  <!-- Value 854 not available for projects -->
                  <!-- Value 855 not available for projects -->
                  <!-- Value 856 not available for projects -->
                  <!-- Value 857 not available for projects -->
                  <!-- Value 858 not available for projects -->
                  <!-- Value 859 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="860" valueTailoring="restrictable">Procedure to move item when in storage</objectValue>
                  <!-- Value 861 not available for projects -->
                  <!-- Value 862 not available for projects -->
                  <!-- Value 863 not available for projects -->
                  <!-- Value 864 not available for projects -->
                  <!-- Value 865 not available for projects -->
                  <!-- Value 866 not available for projects -->
                  <!-- Value 867 not available for projects -->
                  <!-- Value 868 not available for projects -->
                  <!-- Value 869 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="870" valueTailoring="restrictable">Procedure to prepare item for use after storage</objectValue>
                  <objectValue valueForm="single" valueAllowed="871" valueTailoring="restrictable">Set on condition</objectValue>
                  <!-- Value 872 not available for projects -->
                  <!-- Value 873 not available for projects -->
                  <!-- Value 874 not available for projects -->
                  <!-- Value 875 not available for projects -->
                  <!-- Value 876 not available for projects -->
                  <!-- Value 877 not available for projects -->
                  <!-- Value 878 not available for projects -->
                  <!-- Value 879 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="880" valueTailoring="restrictable">Procedure when item got out of storage</objectValue>
                  <!-- Value 881 not available for projects -->
                  <!-- Value 882 not available for projects -->
                  <!-- Value 883 not available for projects -->
                  <!-- Value 884 not available for projects -->
                  <!-- Value 885 not available for projects -->
                  <!-- Value 886 not available for projects -->
                  <!-- Value 887 not available for projects -->
                  <!-- Value 888 not available for projects -->
                  <!-- Value 889 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="890" valueTailoring="restrictable">Life data of item when in storage</objectValue>
                  <!-- Value 891 not available for projects -->
                  <!-- Value 892 not available for projects -->
                  <!-- Value 893 not available for projects -->
                  <!-- Value 894 not available for projects -->
                  <!-- Value 895 not available for projects -->
                  <!-- Value 896 not available for projects -->
                  <!-- Value 897 not available for projects -->
                  <!-- Value 898 not available for projects -->
                  <!-- Value 899 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[8][0-9][A-Z]">Project available</objectValue>
                  <objectValue valueForm="single" valueAllowed="900" valueTailoring="restrictable">Miscellaneous</objectValue>
                  <objectValue valueForm="single" valueAllowed="901" valueTailoring="restrictable">Miscellaneous list of consumables</objectValue>
                  <objectValue valueForm="single" valueAllowed="902" valueTailoring="restrictable">Miscellaneous list of materials</objectValue>
                  <objectValue valueForm="single" valueAllowed="903" valueTailoring="restrictable">Miscellaneous list of expendables</objectValue>
                  <objectValue valueForm="single" valueAllowed="904" valueTailoring="restrictable">Miscellaneous list of special support equipment and tools</objectValue>
                  <objectValue valueForm="single" valueAllowed="905" valueTailoring="restrictable">Miscellaneous list of support equipment and tools</objectValue>
                  <objectValue valueForm="single" valueAllowed="906" valueTailoring="restrictable">Miscellaneous list of software</objectValue>
                  <objectValue valueForm="single" valueAllowed="907" valueTailoring="restrictable">Miscellaneous parts list</objectValue>
                  <!-- Value 908 not available for projects -->
                  <!-- Value 909 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="910" valueTailoring="restrictable">Miscellaneous</objectValue>
                  <objectValue valueForm="single" valueAllowed="911" valueTailoring="restrictable">Illustration</objectValue>
                  <objectValue valueForm="single" valueAllowed="912" valueTailoring="restrictable">Handling procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="913" valueTailoring="restrictable">General maintenance procedure</objectValue>
                  <objectValue valueForm="single" valueAllowed="914" valueTailoring="restrictable">Container data module</objectValue>
                  <objectValue valueForm="single" valueAllowed="915" valueTailoring="restrictable">Facilities</objectValue>
                  <objectValue valueForm="single" valueAllowed="916" valueTailoring="restrictable">Maintenance allocation</objectValue>
                  <objectValue valueForm="single" valueAllowed="917" valueTailoring="restrictable">Non-S1000D publication</objectValue>
                  <!-- Value 918 not available for projects -->
                  <!-- Value 919 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="920" valueTailoring="restrictable">Change = Remove and install</objectValue>
                  <objectValue valueForm="single" valueAllowed="921" valueTailoring="restrictable">Change = Remove and install a new item</objectValue>
                  <objectValue valueForm="single" valueAllowed="922" valueTailoring="restrictable">Change = Remove and install the removed item</objectValue>
                  <objectValue valueForm="single" valueAllowed="923" valueTailoring="restrictable">Change = Disconnect and connect an item</objectValue>
                  <!-- Value 924 not available for projects -->
                  <!-- Value 925 not available for projects -->
                  <!-- Value 926 not available for projects -->
                  <!-- Value 927 not available for projects -->
                  <!-- Value 928 not available for projects -->
                  <!-- Value 929 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="930" valueTailoring="restrictable">Service bulletin</objectValue>
                  <!-- Value 931 not available for projects (Used for service bulletins following the rules in Issue 4.0 or earlier issues) -->
                  <!-- Value 932 not available for projects (Used for service bulletins following the rules in Issue 4.0 or earlier issues) -->
                  <objectValue valueForm="single" valueAllowed="933" valueTailoring="restrictable">Accomplishment procedure - Task set</objectValue>
                  <objectValue valueForm="single" valueAllowed="934" valueTailoring="restrictable">Material information</objectValue>
                  <!-- Value 935 not available for projects -->
                  <!-- Value 936 not available for projects -->
                  <!-- Value 937 not available for projects -->
                  <!-- Value 938 not available for projects -->
                  <!-- Value 939 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="940" valueTailoring="restrictable">Provisioning data</objectValue>
                  <objectValue valueForm="single" valueAllowed="941" valueTailoring="restrictable">Illustrated parts data</objectValue>
                  <objectValue valueForm="single" valueAllowed="942" valueTailoring="restrictable">Numerical index</objectValue>
                  <!-- Value 943 not available for projects -->
                  <!-- Value 944 not available for projects -->
                  <!-- Value 945 not available for projects -->
                  <!-- Value 946 not available for projects -->
                  <!-- Value 947 not available for projects -->
                  <!-- Value 948 not available for projects -->
                  <!-- Value 949 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="950" valueTailoring="restrictable">Composite information</objectValue>
                  <objectValue valueForm="single" valueAllowed="951" valueTailoring="restrictable">Generic process</objectValue>
                  <objectValue valueForm="single" valueAllowed="952" valueTailoring="restrictable">Generic learning content</objectValue>
                  <!-- Value 953 not available for projects -->
                  <!-- Value 954 not available for projects -->
                  <!-- Value 955 not available for projects -->
                  <!-- Value 956 not available for projects -->
                  <!-- Value 957 not available for projects -->
                  <!-- Value 958 not available for projects -->
                  <!-- Value 959 not available for projects -->
                  <!-- Value 960 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="961" valueTailoring="restrictable">Calculation worksheets</objectValue>
                  <!-- Value 962 not available for projects -->
                  <!-- Value 963 not available for projects -->
                  <!-- Value 964 not available for projects -->
                  <!-- Value 965 not available for projects -->
                  <!-- Value 966 not available for projects -->
                  <!-- Value 967 not available for projects -->
                  <!-- Value 968 not available for projects -->
                  <!-- Value 969 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="970" valueTailoring="restrictable">Approved vendor processes</objectValue>
                  <!-- Value 971 not available for projects -->
                  <!-- Value 972 not available for projects -->
                  <!-- Value 973 not available for projects -->
                  <!-- Value 974 not available for projects -->
                  <!-- Value 975 not available for projects -->
                  <!-- Value 976 not available for projects -->
                  <!-- Value 977 not available for projects -->
                  <!-- Value 978 not available for projects -->
                  <!-- Value 979 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="980" valueTailoring="restrictable">Environmental protection, fire-fighting and rescue</objectValue>
                  <objectValue valueForm="single" valueAllowed="981" valueTailoring="restrictable">Air cleaning</objectValue>
                  <objectValue valueForm="single" valueAllowed="982" valueTailoring="restrictable">Sewage treatment</objectValue>
                  <!-- Value 983 not available for projects -->
                  <!-- Value 984 not available for projects -->
                  <!-- Value 985 not available for projects -->
                  <!-- Value 986 not available for projects -->
                  <!-- Value 987 not available for projects -->
                  <!-- Value 988 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="989" valueTailoring="restrictable">Fire-fighting and rescue</objectValue>
                  <objectValue valueForm="single" valueAllowed="990" valueTailoring="restrictable">Neutralization and disposal</objectValue>
                  <objectValue valueForm="single" valueAllowed="991" valueTailoring="restrictable">Neutralization of ordnance</objectValue>
                  <objectValue valueForm="single" valueAllowed="992" valueTailoring="restrictable">Neutralization of substance</objectValue>
                  <!-- Value 993 not available for projects -->
                  <!-- Value 994 not available for projects -->
                  <!-- Value 995 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="996" valueTailoring="restrictable">Disposal of ordnance</objectValue>
                  <objectValue valueForm="single" valueAllowed="997" valueTailoring="restrictable">Disposal of product</objectValue>
                  <objectValue valueForm="single" valueAllowed="998" valueTailoring="restrictable">Disposal of substance</objectValue>
                  <!-- Value 999 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[9][0-9][A-Z]">Project available</objectValue>
                  <objectValue valueForm="single" valueAllowed="C00" valueTailoring="restrictable">Computer systems, software and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="C01" valueTailoring="restrictable">Miscellaneous list of consumables associated with computer systems, software and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="C02" valueTailoring="restrictable">Miscellaneous list of materials associated with computer systems, software and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="C03" valueTailoring="restrictable">Miscellaneous list of expendables associated with computer systems, software and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="C04" valueTailoring="restrictable">Miscellaneous list of special support equipment and tools associated with computer systems, software and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="C05" valueTailoring="restrictable">Miscellaneous list of support equipment and tools associated with computer systems, software and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="C06" valueTailoring="restrictable">Miscellaneous list of software associated with computer systems, software and data</objectValue>
                  <objectValue valueForm="single" valueAllowed="C07" valueTailoring="restrictable">Miscellaneous parts list associated with computer systems, software and data</objectValue>
                  <!-- Value C08 not available for projects -->
                  <!-- Value C09 not available for projects -->
                  <!-- Value C10 not available for projects -->
                  <!-- Value C11 not available for projects -->
                  <!-- Value C12 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="C13" valueTailoring="restrictable">Notes</objectValue>
                  <objectValue valueForm="single" valueAllowed="C14" valueTailoring="restrictable">Problem handling</objectValue>
                  <objectValue valueForm="single" valueAllowed="C15" valueTailoring="restrictable">Summary of content</objectValue>
                  <!-- Value C16 not available for projects -->
                  <!-- Value C17 not available for projects -->
                  <!-- Value C18 not available for projects -->
                  <!-- Value C19 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="C20" valueTailoring="restrictable">System administration</objectValue>
                  <objectValue valueForm="single" valueAllowed="C21" valueTailoring="restrictable">System monitoring</objectValue>
                  <objectValue valueForm="single" valueAllowed="C22" valueTailoring="restrictable">Description of command</objectValue>
                  <objectValue valueForm="single" valueAllowed="C23" valueTailoring="restrictable">Connect hardware</objectValue>
                  <!-- Value C24 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="C25" valueTailoring="restrictable">System recovery</objectValue>
                  <objectValue valueForm="single" valueAllowed="C26" valueTailoring="restrictable">Backup and restore</objectValue>
                  <objectValue valueForm="single" valueAllowed="C27" valueTailoring="restrictable">Reboot</objectValue>
                  <!-- Value C28 not available for projects -->
                  <!-- Value C29 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="C30" valueTailoring="restrictable">Coordinate</objectValue>
                  <objectValue valueForm="single" valueAllowed="C31" valueTailoring="restrictable">Defragmentation</objectValue>
                  <objectValue valueForm="single" valueAllowed="C32" valueTailoring="restrictable">Input/Output media</objectValue>
                  <objectValue valueForm="single" valueAllowed="C33" valueTailoring="restrictable">Disk mirroring</objectValue>
                  <objectValue valueForm="single" valueAllowed="C34" valueTailoring="restrictable">Clear interference</objectValue>
                  <objectValue valueForm="single" valueAllowed="C35" valueTailoring="restrictable">Time check</objectValue>
                  <objectValue valueForm="single" valueAllowed="C36" valueTailoring="restrictable">Compatibility check</objectValue>
                  <!-- Value C37 not available for projects -->
                  <!-- Value C38 not available for projects -->
                  <!-- Value C39 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[C][0-3][A-Z]">Project available</objectValue>
                  <!-- Value C40 not available for projects -->
                  <!-- Value C41 not available for projects -->
                  <!-- Value C42 not available for projects -->
                  <!-- Value C43 not available for projects -->
                  <!-- Value C44 not available for projects -->
                  <!-- Value C45 not available for projects -->
                  <!-- Value C46 not available for projects -->
                  <!-- Value C47 not available for projects -->
                  <!-- Value C48 not available for projects -->
                  <!-- Value C49 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="C50" valueTailoring="restrictable">Manage data</objectValue>
                  <objectValue valueForm="single" valueAllowed="C51" valueTailoring="restrictable">Move data</objectValue>
                  <objectValue valueForm="single" valueAllowed="C52" valueTailoring="restrictable">Manipulate/Use data</objectValue>
                  <objectValue valueForm="single" valueAllowed="C53" valueTailoring="restrictable">Description of data storage</objectValue>
                  <!-- Value C54 not available for projects -->
                  <!-- Value C55 not available for projects -->
                  <!-- Value C56 not available for projects -->
                  <!-- Value C57 not available for projects -->
                  <!-- Value C58 not available for projects -->
                  <!-- Value C59 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="C60" valueTailoring="restrictable">Programming information</objectValue>
                  <objectValue valueForm="single" valueAllowed="C61" valueTailoring="restrictable">Program flow chart</objectValue>
                  <objectValue valueForm="single" valueAllowed="C62" valueTailoring="restrictable">Processing reference guide</objectValue>
                  <!-- Value C63 not available for projects -->
                  <!-- Value C64 not available for projects -->
                  <!-- Value C65 not available for projects -->
                  <!-- Value C66 not available for projects -->
                  <!-- Value C67 not available for projects -->
                  <!-- Value C68 not available for projects -->
                  <!-- Value C69 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="C70" valueTailoring="restrictable">Security and privacy</objectValue>
                  <!-- Value C71 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="C72" valueTailoring="restrictable">Security information</objectValue>
                  <objectValue valueForm="single" valueAllowed="C73" valueTailoring="restrictable">Security procedures</objectValue>
                  <objectValue valueForm="single" valueAllowed="C74" valueTailoring="restrictable">List of security/classification codes</objectValue>
                  <objectValue valueForm="single" valueAllowed="C75" valueTailoring="restrictable">Access control</objectValue>
                  <!-- Value C76 not available for projects -->
                  <!-- Value C77 not available for projects -->
                  <!-- Value C78 not available for projects -->
                  <!-- Value C79 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[C][5-7][A-Z]">Project available</objectValue>
                  <!-- Value C80 not available for projects -->
                  <!-- Value C81 not available for projects -->
                  <!-- Value C82 not available for projects -->
                  <!-- Value C83 not available for projects -->
                  <!-- Value C84 not available for projects -->
                  <!-- Value C85 not available for projects -->
                  <!-- Value C86 not available for projects -->
                  <!-- Value C87 not available for projects -->
                  <!-- Value C88 not available for projects -->
                  <!-- Value C89 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="C90" valueTailoring="restrictable">Miscellaneous</objectValue>
                  <objectValue valueForm="single" valueAllowed="C91" valueTailoring="restrictable">Quality assurance</objectValue>
                  <objectValue valueForm="single" valueAllowed="C92" valueTailoring="restrictable">Vendor information</objectValue>
                  <!-- Value C93 not available for projects -->
                  <!-- Value C94 not available for projects -->
                  <objectValue valueForm="single" valueAllowed="C95" valueTailoring="restrictable">Naming conventions</objectValue>
                  <objectValue valueForm="single" valueAllowed="C96" valueTailoring="restrictable">Technical requirements</objectValue>
                  <!-- Value C97 not available for projects -->
                  <!-- Value C98 not available for projects -->
                  <!-- Value C99 not available for projects -->
                  <!-- Values YYA~YYZ available for projects in case YY has been allocated by S1000D -->
                  <objectValue valueForm="pattern" valueAllowed="[C][9][A-Z]">Project available</objectValue>
                </structureObjectRule>
                <!-- 3.9.6.1 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00181" />
                  <objectPath allowedObjectFlag="2">//@accessPointTypeValue</objectPath>
                  <objectUse>Attribute accessPointTypeValue - Access point type (Chap 3.9.6.1, Table 2)</objectUse>
                  <objectValue valueForm="single" valueAllowed="accpnl01" valueTailoring="lexical">Access is a door</objectValue>
                  <objectValue valueForm="single" valueAllowed="accpnl02" valueTailoring="lexical">Access is a panel</objectValue>
                  <objectValue valueForm="single" valueAllowed="accpnl03" valueTailoring="lexical">Access is an electrical panel</objectValue>
                  <objectValue valueForm="single" valueAllowed="accpnl04" valueTailoring="lexical">Access is a hatch</objectValue>
                  <objectValue valueForm="single" valueAllowed="accpnl05" valueTailoring="lexical">Access is a fillet</objectValue>
                  <!-- Values within range accpnl51~accpnl99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="accpnl51~accpnl99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00182" />
                  <objectPath allowedObjectFlag="2">//@acronymType</objectPath>
                  <objectUse>Attribute acronymType - Type of acronym or abbreviation (Chap 3.9.6.1, Table 3)</objectUse>
                  <objectValue valueForm="single" valueAllowed="at01" valueTailoring="lexical">Acronym (Candidate for list of abbreviations)</objectValue>
                  <objectValue valueForm="single" valueAllowed="at02" valueTailoring="lexical">Term (Candidate for list of terms)</objectValue>
                  <objectValue valueForm="single" valueAllowed="at03" valueTailoring="lexical">Symbol (Candidate for list of symbols)</objectValue>
                  <objectValue valueForm="single" valueAllowed="at04" valueTailoring="lexical">Spec (Candidate for list of applicable specs)</objectValue>
                  <!-- Values within range at51~at99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="at51~at99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00251"/>
                  <objectPath allowedObjectFlag="2">//@actionIdentType</objectPath>
                  <objectUse>Attribute actionIdentType - Classification action (Chap 3.9.6.1, Table 4).</objectUse>
                  <objectValue valueForm="single" valueAllowed="ai01" valueTailoring="lexical">Classified On</objectValue>
                  <objectValue valueForm="single" valueAllowed="ai02" valueTailoring="lexical">Declassify On</objectValue>
                  <objectValue valueForm="single" valueAllowed="ai03" valueTailoring="lexical">Downgrade On</objectValue>
                  <objectValue valueForm="single" valueAllowed="ai04" valueTailoring="lexical">Upgrade On</objectValue>
                  <objectValue valueForm="single" valueAllowed="ai05" valueTailoring="lexical">Downgrade securityClassification to 03</objectValue>
                  <objectValue valueForm="single" valueAllowed="ai06" valueTailoring="lexical">Downgrade securityClassification to 04</objectValue>
                  <objectValue valueForm="single" valueAllowed="ai07" valueTailoring="lexical">Downgrade securityClassification to 05</objectValue>
                  <objectValue valueForm="single" valueAllowed="ai08" valueTailoring="lexical">Upgrade securityClassification to 03</objectValue>
                  <objectValue valueForm="single" valueAllowed="ai09" valueTailoring="lexical">Upgrade securityClassification to 04</objectValue>
                  <objectValue valueForm="single" valueAllowed="ai10" valueTailoring="lexical">Upgrade securityClassification to 05</objectValue>
                  <!-- Values within range ai51~ai99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="ai51~ai99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00183" />
                  <objectPath allowedObjectFlag="2">//@barCodeSymbology</objectPath>
                  <objectUse>Attribute barCodeSymbology - Symbology/rendering applied to a bar code value (Chap 3.9.6.1, Table 5)</objectUse>
                  <objectValue valueForm="single" valueAllowed="bcs01" valueTailoring="lexical">Codabar</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs02" valueTailoring="lexical">Code 11</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs03" valueTailoring="lexical">EAN-13</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs04" valueTailoring="lexical">EAN-8</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs05" valueTailoring="lexical">Industrial 2 of 5</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs06" valueTailoring="lexical">Interleaved 2 of 5</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs07" valueTailoring="lexical">MSI</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs08" valueTailoring="lexical">Plessey</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs09" valueTailoring="lexical">POSTNET</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs10" valueTailoring="lexical">UPC-A</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs11" valueTailoring="lexical">Standard 2 of 5</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs12" valueTailoring="lexical">UPC-E</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs13" valueTailoring="lexical">Code 128</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs14" valueTailoring="lexical">Code 39</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs15" valueTailoring="lexical">Code 93</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs16" valueTailoring="lexical">LOGMARS</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs17" valueTailoring="lexical">PDF417</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs18" valueTailoring="lexical">DataMatrix</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs19" valueTailoring="lexical">Maxicode</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs20" valueTailoring="lexical">QR Code</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs21" valueTailoring="lexical">Data Code</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs22" valueTailoring="lexical">Code 49</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs23" valueTailoring="lexical">16K</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs24" valueTailoring="lexical">Bookland EAN</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs25" valueTailoring="lexical">ISSN and the SISAC Barcode</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs26" valueTailoring="lexical">OPC</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs27" valueTailoring="lexical">UCC/EAN-128</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs28" valueTailoring="lexical">UPC Shipping Container Symbol: ITF-14</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs29" valueTailoring="lexical">PLANET</objectValue>
                  <objectValue valueForm="single" valueAllowed="bcs30" valueTailoring="lexical">Intelligent Mail (USPS4CB)</objectValue>
                  <!-- Values within range bcs51~bcs99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="bcs51~bcs99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00267"/>
                  <objectPath allowedObjectFlag="2">//@brDecisionPointPriority</objectPath>
                  <objectUse>Attribute brDecisionPointPriority - Business rule decision point priority (Chap 3.9.6.1, Table 6)</objectUse>
                  <objectValue valueForm="single" valueAllowed="brpr01"	valueTailoring="lexical">Highest BR priority</objectValue>
                  <objectValue valueForm="single" valueAllowed="brpr02"	valueTailoring="lexical">Next lower level of BR priority</objectValue>
                  <objectValue valueForm="single" valueAllowed="brpr03"	valueTailoring="lexical">Next lower level of BR priority</objectValue>
                  <objectValue valueForm="single" valueAllowed="brpr04"	valueTailoring="lexical">Next lower level of BR priority</objectValue>
                  <objectValue valueForm="single" valueAllowed="brpr05"	valueTailoring="lexical">Lowest level of BR priority</objectValue>
                  <!-- Values within range brpr51~brpr99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="brpr51~brpr99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <!-- NOTE! This attribute must be synchronized with attribute defaultBrSeverityLevel -->
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00252"/>
                  <objectPath allowedObjectFlag="2">//@brSeverityLevel</objectPath>
                  <objectUse>Attribute brSeverityLevel - Business rule breach severity level (Chap 3.9.6.1, Table 7)</objectUse>
                  <objectValue valueForm="single" valueAllowed="brsl01" valueTailoring="lexical">Most severe</objectValue>
                  <objectValue valueForm="single" valueAllowed="brsl02" valueTailoring="lexical">Medium severity</objectValue>
                  <objectValue valueForm="single" valueAllowed="brsl03" valueTailoring="lexical">Least severe</objectValue>
                  <!-- Values within range brsl51~brsl99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="brsl51~brsl99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00262"/>
                  <objectPath allowedObjectFlag="2">//@brStatus</objectPath>
                  <objectUse>Attribute brStatus - Business rule quality assurance status (Chap 3.9.6.1, Table 8)</objectUse>
                  <objectValue valueForm="single" valueAllowed="brst01" valueTailoring="lexical">Unverified</objectValue>
                  <objectValue valueForm="single" valueAllowed="brst02" valueTailoring="lexical">First verified</objectValue>
                  <objectValue valueForm="single" valueAllowed="brst03" valueTailoring="lexical">Second verified</objectValue>
                  <!-- Values within range brst51~brst99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="brst51~brst99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00184" />
                  <objectPath allowedObjectFlag="2">//@cancelCaption</objectPath>
                  <objectUse>Attribute cancelCaption - Caption for dialog cancel function (Chap 3.9.6.1, Table 9)</objectUse>
                  <objectValue valueForm="single" valueAllowed="ca01" valueTailoring="lexical">Sets the caption to /CANCEL/</objectValue>
                  <objectValue valueForm="single" valueAllowed="ca02" valueTailoring="lexical">Sets the caption to /ABORT/</objectValue>
                  <objectValue valueForm="single" valueAllowed="ca03" valueTailoring="lexical">Sets the caption to /NO/</objectValue>
                  <objectValue valueForm="single" valueAllowed="ca04" valueTailoring="lexical">Sets the caption to /END/</objectValue>
                  <objectValue valueForm="single" valueAllowed="ca05" valueTailoring="lexical">Sets the caption to /QUIT/</objectValue>
                  <!-- Values within range ca51~ca99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="ca51~ca99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00185" />
                  <objectPath allowedObjectFlag="2">//@caveat</objectPath>
                  <objectUse>Attribute caveat - National caveat (Chap 3.9.6.1, Table 10)</objectUse>
                  <!-- Values within range cv51~cv99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="cv51~cv99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00186" />
                  <objectPath allowedObjectFlag="2">//@checkListCategory</objectPath>
                  <objectUse>Attribute checkListCategory - Check list category (Chap 3.9.6.1, Table 11)</objectUse>
                  <objectValue valueForm="single" valueAllowed="clc01" valueTailoring="lexical">Preventive maintenance inspection form</objectValue>
                  <objectValue valueForm="single" valueAllowed="clc02" valueTailoring="lexical">Preventive maintenance checks and services</objectValue>
                  <objectValue valueForm="single" valueAllowed="clc03" valueTailoring="lexical">Schematic</objectValue>
                  <!-- Values within range clc51~clc99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="clc51~clc99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00187" />
                  <objectPath allowedObjectFlag="2">//@circuitBreakerRefType</objectPath>
                  <objectUse>Attribute circuitBreakerRefType - Circuit Breaker Reference Type (Chap 3.9.6.1, Table 12)</objectUse>
                  <objectValue valueForm="single" valueAllowed="cbr01" valueTailoring="lexical">Reference to the primary circuit breaker</objectValue>
                  <objectValue valueForm="single" valueAllowed="cbr02" valueTailoring="lexical">Reference to the provisioned circuit breaker</objectValue>
                  <!-- Values within range cbr51~cbr99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="cbr51~cbr99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00188" />
                  <objectPath allowedObjectFlag="2">//@circuitBreakerType</objectPath>
                  <objectUse>Attribute circuitBreakerType - Type of circuit breaker  (Chap 3.9.6.1, Table 13)</objectUse>
                  <objectValue valueForm="single" valueAllowed="cbt01" valueTailoring="lexical">Electronic circuit breaker</objectValue>
                  <objectValue valueForm="single" valueAllowed="cbt02" valueTailoring="lexical">Electromechanical circuit breaker</objectValue>
                  <objectValue valueForm="single" valueAllowed="cbt03" valueTailoring="lexical">Clipped circuit breaker</objectValue>
                  <!-- Values within range cbt51~cbt99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="cbt51~cbt99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00189" />
                  <objectPath allowedObjectFlag="2">//@color</objectPath>
                  <objectUse>Attribute color - Caption color (Chap 3.9.6.1, Table 14)</objectUse>
                  <objectValue valueForm="single" valueAllowed="co00" valueTailoring="lexical">None</objectValue>
                  <objectValue valueForm="single" valueAllowed="co01" valueTailoring="lexical">Green</objectValue>
                  <objectValue valueForm="single" valueAllowed="co02" valueTailoring="lexical">Amber</objectValue>
                  <objectValue valueForm="single" valueAllowed="co03" valueTailoring="lexical">Yellow</objectValue>
                  <objectValue valueForm="single" valueAllowed="co04" valueTailoring="lexical">Red</objectValue>
                  <objectValue valueForm="single" valueAllowed="co07" valueTailoring="lexical">White</objectValue>
                  <objectValue valueForm="single" valueAllowed="co08" valueTailoring="lexical">Grey</objectValue>
                  <objectValue valueForm="single" valueAllowed="co09" valueTailoring="lexical">Clear</objectValue>
                  <objectValue valueForm="single" valueAllowed="co10" valueTailoring="lexical">Black</objectValue>
                  <!-- Values within range co51~co99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="co51~co99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00190" />
                  <objectPath allowedObjectFlag="2">//@commentPriorityCode</objectPath>
                  <objectUse>Attribute commentPriorityCode - Priority level of a comment (Chap 3.9.6.1, Table 15)</objectUse>
                  <objectValue valueForm="single" valueAllowed="cp01" valueTailoring="lexical">Routine</objectValue>
                  <objectValue valueForm="single" valueAllowed="cp02" valueTailoring="lexical">Emergency</objectValue>
                  <objectValue valueForm="single" valueAllowed="cp03" valueTailoring="lexical">Safety critical</objectValue>
                  <!-- Values within range cp51~cp99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="cp51~cp99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00191" />
                  <objectPath allowedObjectFlag="2">//@commercialClassification</objectPath>
                  <objectUse>Attribute commercialClassification - Commercial security classification (Chap 3.9.6.1, Table 16)</objectUse>
                  <!-- Values within range cc51~cc99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="cc51~cc99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule reasonForUpdateRefIds="CPF2015-016AA" changeType="add" changeMark="1">
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00270" />
                  <objectPath allowedObjectFlag="2">//@controlAuthorityType</objectPath>
                  <objectUse>Attribute controlAuthorityType – Type of control authority (Chap 3.9.6.1, Table 17)</objectUse>
                  <objectValue valueForm="single" valueAllowed="cat01" valueTailoring="lexical">Airworthiness Directive (AD)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat02" valueTailoring="lexical">Alternate Method of Compliance (AMOC)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat03" valueTailoring="lexical">Airworthiness Limitation (AWL)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat04" valueTailoring="lexical">Autoland Category III (CAT III)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat05" valueTailoring="lexical">Critical Design Configuration Control Limitation (CDCCL)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat06" valueTailoring="lexical">Certification Maintenance Requirement (CMR)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat07" valueTailoring="lexical">Extended Operations (ETOPS)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat08" valueTailoring="lexical">Electrical Wiring Interconnection Systems (EWIS)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat09" valueTailoring="lexical">Letter of Investigation (LOI)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat10" valueTailoring="lexical">Required Inspection Item (RII)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat11" valueTailoring="lexical">Reduced Vertical Separation Minimum (RVSM)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat12" valueTailoring="lexical">Special FAR for Fuel Tank Flammability (SFAR 88)</objectValue>
                  <objectValue valueForm="single" valueAllowed="cat13" valueTailoring="lexical">Equipment Owner or Customer Originated Change (COC)</objectValue>
                  <!-- Values within range cat51~cat99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="cat51~cat99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00192" />
                  <objectPath allowedObjectFlag="2">//@crewMemberType</objectPath>
                  <objectUse>Attribute crewMemberType - Type of crew member required for drill or procedural step (Chap 3.9.6.1, Table 18)</objectUse>
                  <objectValue valueForm="single" valueAllowed="cm01" valueTailoring="lexical">All</objectValue>
                  <objectValue valueForm="single" valueAllowed="cm02" valueTailoring="lexical">Pilot</objectValue>
                  <objectValue valueForm="single" valueAllowed="cm03" valueTailoring="lexical">Co-pilot</objectValue>
                  <objectValue valueForm="single" valueAllowed="cm04" valueTailoring="lexical">Navigator</objectValue>
                  <objectValue valueForm="single" valueAllowed="cm05" valueTailoring="lexical">Engineer</objectValue>
                  <objectValue valueForm="single" valueAllowed="cm06" valueTailoring="lexical">Ground crew</objectValue>
                  <objectValue valueForm="single" valueAllowed="cm07" valueTailoring="lexical">Load master</objectValue>
                  <objectValue valueForm="single" valueAllowed="cm08" valueTailoring="lexical">Cabin supervisor</objectValue>
                  <!-- Values within range cm51~cm99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="cm51~cm99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00193" />
                  <objectPath allowedObjectFlag="2">//@crewStepCondition</objectPath>
                  <objectUse>Attribute crewStepCondition - Crew step condition (Chap 3.9.6.1, Table 19)</objectUse>
                  <!-- "csc01" - Equipment is installed or available -->
                  <!-- "csc02" - A detailed procedure for the step is located in the performance section of the condensed checklist -->
                  <!-- "csc03" - Performance of the step is mandatory for all through-flights used for combat/tactical operations -->
                  <!-- "csc04" - A step that is mandatory for night flights -->
                  <!-- "csc05" - A task or step required by the operator manual -->
                  <!-- Values within range csc51~csc99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="pattern" valueAllowed="((csc0[1-5])|(csc5[1-9])|(csc[6-9][0-9]))((\ )+((csc0[1-5])|(csc5[1-9])|(csc[6-9][0-9])))*"/>
                </structureObjectRule>
                <structureObjectRule>
                  <!-- NOTE! This attribute must be synchronized with attribute brSeverityLevel -->
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00263"/>
                  <objectPath allowedObjectFlag="2">//@defaultBrSeverityLevel</objectPath>
                  <objectUse>Attribute defaultBrSeverityLevel - Default business rule breach severity level (Chap 3.9.6.1, Table 20)</objectUse>
                  <objectValue valueForm="single" valueAllowed="brsl01" valueTailoring="lexical">Most severe</objectValue>
                  <objectValue valueForm="single" valueAllowed="brsl02" valueTailoring="lexical">Medium severity</objectValue>
                  <objectValue valueForm="single" valueAllowed="brsl03" valueTailoring="lexical">Least severe</objectValue>
                  <!-- Values within range brsl51~brsl99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="brsl51~brsl99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00194" />
                  <objectPath allowedObjectFlag="2">//@drillType</objectPath>
                  <objectUse>Attribute drillType - Type of aircrew drill (Chap 3.9.6.1, Table 21)</objectUse>
                  <objectValue valueForm="single" valueAllowed="dt00" valueTailoring="lexical">None</objectValue>
                  <objectValue valueForm="single" valueAllowed="dt01" valueTailoring="lexical">Green</objectValue>
                  <objectValue valueForm="single" valueAllowed="dt02" valueTailoring="lexical">Amber</objectValue>
                  <objectValue valueForm="single" valueAllowed="dt03" valueTailoring="lexical">Yellow</objectValue>
                  <objectValue valueForm="single" valueAllowed="dt04" valueTailoring="lexical">Red</objectValue>
                  <objectValue valueForm="single" valueAllowed="dt05" valueTailoring="lexical">Orange</objectValue>
                  <objectValue valueForm="single" valueAllowed="dt06" valueTailoring="lexical">Blue</objectValue>
                  <!-- Values within range dt51~dt99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="dt51~dt99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00195" />
                  <objectPath allowedObjectFlag="2">//@emphasisType</objectPath>
                  <objectUse>Attribute emphasisType - Type of emphasis (Chap 3.9.6.1, Table 22)</objectUse>
                  <objectValue valueForm="single" valueAllowed="em01" valueTailoring="lexical">Bold</objectValue>
                  <objectValue valueForm="single" valueAllowed="em02" valueTailoring="lexical">Italic (only for legacy data, see Chap 3.9.1)</objectValue>
                  <objectValue valueForm="single" valueAllowed="em03" valueTailoring="lexical">Underline (only for legacy data, see Chap 3.9.1)</objectValue>
                  <objectValue valueForm="single" valueAllowed="em04" valueTailoring="lexical">Overline (only for marking vectors)</objectValue>
                  <objectValue valueForm="single" valueAllowed="em05" valueTailoring="lexical">Strikethrough (not to be used to mark deleted text)</objectValue>
                  <!-- Values within range em51~em99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="em51~em99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00196" />
                  <objectPath allowedObjectFlag="2">//@frontMatterInfoType</objectPath>
                  <objectUse>Attribute frontMatterInfoType - Type of front matter title page info block (Chap 3.9.6.1, Table 23)</objectUse>
                  <objectValue valueForm="single" valueAllowed="fmi01" valueTailoring="lexical">Generic front matter info block</objectValue>
                  <objectValue valueForm="single" valueAllowed="fmi02" valueTailoring="lexical">Manufacturers information</objectValue>
                  <objectValue valueForm="single" valueAllowed="fmi03" valueTailoring="lexical">Reporting errors and recommending improvements statement</objectValue>
                  <objectValue valueForm="single" valueAllowed="fmi04" valueTailoring="lexical">Availability statement</objectValue>
                  <objectValue valueForm="single" valueAllowed="fmi05" valueTailoring="lexical">Preventive maintenance service warning</objectValue>
                  <objectValue valueForm="single" valueAllowed="fmi06" valueTailoring="lexical">General purpose notice</objectValue>
                  <objectValue valueForm="single" valueAllowed="fmi07" valueTailoring="lexical">Ozone depleting chemical information</objectValue>
                  <objectValue valueForm="single" valueAllowed="fmi08" valueTailoring="lexical">Hazardous materials information</objectValue>
                  <!-- Values within range fmi51~fmi99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="fmi51~fmi99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00197" />
                  <objectPath allowedObjectFlag="2">//@frontMatterType</objectPath>
                  <objectUse>Attribute frontMatterType - Type of front matter content (Chap 3.9.6.1, Table 24)</objectUse>
                  <objectValue valueForm="single" valueAllowed="fm01" valueTailoring="lexical">LOEP - List of effective pages</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm02" valueTailoring="lexical">LOEDM - List of effective data modules</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm03" valueTailoring="lexical">HIGH - Highlights</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm04" valueTailoring="lexical">HIGH - Highlights with updating instructions</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm05" valueTailoring="lexical">Publication list data modules</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm06" valueTailoring="lexical">LOI - List of illustrations</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm07" valueTailoring="lexical">LOA - List of abbreviations</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm08" valueTailoring="lexical">LOT - List of terms </objectValue>
                  <objectValue valueForm="single" valueAllowed="fm09" valueTailoring="lexical">LOS - List of symbols</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm10" valueTailoring="lexical">TSR - Technical standard record</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm11" valueTailoring="lexical">LOM - List of modifications</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm12" valueTailoring="lexical">LOASD - List of applicable specifications and documentation</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm13" valueTailoring="lexical">LOW - List of warnings</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm14" valueTailoring="lexical">LOC - List of cautions</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm15" valueTailoring="lexical">LOSE - List of support equipment</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm16" valueTailoring="lexical">LOSU - List of supplies</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm17" valueTailoring="lexical">LOSP - List of spares</objectValue>
                  <objectValue valueForm="single" valueAllowed="fm18" valueTailoring="lexical">LOV - List of vendors</objectValue>
                  <!-- Values within range fm51~fm99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="fm51~fm99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00198" />
                  <objectPath allowedObjectFlag="2">//@function</objectPath>
                  <objectUse>Attribute function - Maintenance function (Chap 3.9.6.1, Table 25)</objectUse>
                  <objectValue valueForm="single" valueAllowed="ft00" valueTailoring="lexical">None</objectValue>
                  <objectValue valueForm="single" valueAllowed="ft01" valueTailoring="lexical">Inspect</objectValue>
                  <objectValue valueForm="single" valueAllowed="ft02" valueTailoring="lexical">Test</objectValue>
                  <objectValue valueForm="single" valueAllowed="ft03" valueTailoring="lexical">Service</objectValue>
                  <objectValue valueForm="single" valueAllowed="ft04" valueTailoring="lexical">Adjust</objectValue>
                  <objectValue valueForm="single" valueAllowed="ft05" valueTailoring="lexical">Align</objectValue>
                  <objectValue valueForm="single" valueAllowed="ft06" valueTailoring="lexical">Calibrate</objectValue>
                  <objectValue valueForm="single" valueAllowed="ft07" valueTailoring="lexical">Remove/Install</objectValue>
                  <objectValue valueForm="single" valueAllowed="ft08" valueTailoring="lexical">Replace</objectValue>
                  <objectValue valueForm="single" valueAllowed="ft09" valueTailoring="lexical">Repair</objectValue>
                  <objectValue valueForm="single" valueAllowed="ft10" valueTailoring="lexical">Overhaul</objectValue>
                  <objectValue valueForm="single" valueAllowed="ft11" valueTailoring="lexical">Rebuild</objectValue>
                  <!-- Values within range ft51~ft99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="ft51~ft99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00199" />
                  <objectPath allowedObjectFlag="2">//@functionalItemRefType</objectPath>
                  <objectUse>Attribute functionalItemRefType - Functional Item Reference Type (Chap 3.9.6.1, Table 26)</objectUse>
                  <objectValue valueForm="single" valueAllowed="fir01" valueTailoring="lexical">Reference to the card functional item</objectValue>
                  <objectValue valueForm="single" valueAllowed="fir02" valueTailoring="lexical">Reference to soft functional item (for hard functional items)</objectValue>
                  <objectValue valueForm="single" valueAllowed="fir03" valueTailoring="lexical">Reference to LRI functional items (for LRU functional items)</objectValue>
                  <objectValue valueForm="single" valueAllowed="fir04" valueTailoring="lexical">Reference to the Shunt Functional Item (for a Circuit Breaker)</objectValue>
                  <objectValue valueForm="single" valueAllowed="fir05" valueTailoring="lexical">Reference to the mate Equipment/Connector Functional Item (for an other Equipment/Connector Functional Item)</objectValue>
                  <objectValue valueForm="single" valueAllowed="fir06" valueTailoring="lexical">Reference to the equipment functional item, electrically protected by a given circuit breaker</objectValue>
                  <objectValue valueForm="single" valueAllowed="fir07" valueTailoring="lexical">Reference to the harness functional item, for an equipment functional item </objectValue>
                  <!-- Values within range fir51~fir99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="fir51~fir99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00200" />
                  <objectPath allowedObjectFlag="2">//@functionalItemType</objectPath>
                  <objectUse>Attribute functionalItemType - Type of functional item (Chap 3.9.6.1, Table 27)</objectUse>
                  <objectValue valueForm="single" valueAllowed="fit01" valueTailoring="lexical">Exact functional item</objectValue>
                  <objectValue valueForm="single" valueAllowed="fit02" valueTailoring="lexical">Family functional item</objectValue>
                  <!-- Values within range fit51~fit99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="fit51~fit99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00201" />
                  <objectPath allowedObjectFlag="2">//@genericPropertyType</objectPath>
                  <objectUse>Attribute genericPropertyType - Generic property type (Chap 3.9.6.1, Table 28)</objectUse>
                  <objectValue valueForm="single" valueAllowed="gpt01" valueTailoring="lexical">Passenger comfort affected</objectValue>
                  <objectValue valueForm="single" valueAllowed="gpt02" valueTailoring="lexical">Reliability affected</objectValue>
                  <objectValue valueForm="single" valueAllowed="gpt03" valueTailoring="lexical">Cost saving</objectValue>
                  <objectValue valueForm="single" valueAllowed="gpt04" valueTailoring="lexical">Structural life extension</objectValue>
                  <objectValue valueForm="single" valueAllowed="gpt05" valueTailoring="lexical">Cancels inspection Service Bulletin</objectValue>
                  <objectValue valueForm="single" valueAllowed="gpt06" valueTailoring="lexical">Product operation affected</objectValue>
                  <objectValue valueForm="single" valueAllowed="gpt07" valueTailoring="lexical">LROPS affected</objectValue>
                  <objectValue valueForm="single" valueAllowed="gpt08" valueTailoring="lexical">ETOPS affected</objectValue>
                  <objectValue valueForm="single" valueAllowed="gpt09" valueTailoring="lexical">Potential Airworthiness Directive</objectValue>
                  <objectValue valueForm="single" valueAllowed="gpt10" valueTailoring="lexical">Disposition for removed spare that can be on customer shop</objectValue>
                  <objectValue valueForm="single" valueAllowed="gpt11" valueTailoring="lexical">Support code for removed spare</objectValue>
                  <!-- Values within range gpt51~gpt99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="gpt51~gpt99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00202" />
                  <objectPath allowedObjectFlag="2">//@hazardousClassValue</objectPath>
                  <objectUse>Attribute hazardousClassValue - The class value of hazard (Chap 3.9.6.1, Table 29)</objectUse>
                  <objectValue valueForm="single" valueAllowed="hz01" valueTailoring="lexical">Explosive</objectValue>
                  <objectValue valueForm="single" valueAllowed="hz02" valueTailoring="lexical">Compressed gases</objectValue>
                  <objectValue valueForm="single" valueAllowed="hz03" valueTailoring="lexical">Flammable liquids</objectValue>
                  <!-- Values within range hz51~hz99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="hz51~hz99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00253"/>
                  <objectPath allowedObjectFlag="2">//@icnInfoItemType</objectPath>
                  <objectUse>Attribute icnInfoItemType - Type of ICN metadata item (Chap 3.9.6.1, Table 30)</objectUse>
                  <objectValue valueForm="single" valueAllowed="iiit01" valueTailoring="lexical">Unspecified</objectValue>
                  <objectValue valueForm="single" valueAllowed="iiit02" valueTailoring="lexical">Size in kb</objectValue>
                  <objectValue valueForm="single" valueAllowed="iiit03" valueTailoring="lexical">Duration in s</objectValue>
                  <objectValue valueForm="single" valueAllowed="iiit04" valueTailoring="lexical">Default width in mm</objectValue>
                  <objectValue valueForm="single" valueAllowed="iiit05" valueTailoring="lexical">Default height in mm </objectValue>
                  <objectValue valueForm="single" valueAllowed="iiit06" valueTailoring="lexical">Transcript</objectValue>
                  <!-- Values within range iiit51~iiit99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="iiit51~iiit99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00255"/>
                  <objectPath allowedObjectFlag="2">//@icnResourceFileType</objectPath>
                  <objectUse>Attribute icnResourceFileType - Type of resource files associated to the ICN (Chap 3.9.6.1, Table 31)</objectUse>
                  <!-- Values within range irft51~irft99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="irft51~irft99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00254"/>
                  <objectPath allowedObjectFlag="2">//@icnSourceFileType</objectPath>
                  <objectUse>Attribute icnSourceFileType - Type of source files associated to the ICN (Chap 3.9.6.1, Table 32)</objectUse>
                  <objectValue valueForm="single" valueAllowed="isft01" valueTailoring="lexical">Unspecified</objectValue>
                  <!-- Values within range isft51~isft99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="isft51~isft99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00203" />
                  <objectPath allowedObjectFlag="2">//@installationLocationType</objectPath>
                  <objectUse>Attribute installationLocationType - Type of install location (Chap 3.9.6.1, Table 33)</objectUse>
                  <objectValue valueForm="single" valueAllowed="instloctyp02" valueTailoring="lexical">Section</objectValue>
                  <objectValue valueForm="single" valueAllowed="instloctyp03" valueTailoring="lexical">Station</objectValue>
                  <objectValue valueForm="single" valueAllowed="instloctyp04" valueTailoring="lexical">Water line</objectValue>
                  <objectValue valueForm="single" valueAllowed="instloctyp05" valueTailoring="lexical">Buttock line</objectValue>
                  <!-- Values within range instloctyp51~instloctyp99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="instloctyp51~instloctyp99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00204" />
                  <objectPath allowedObjectFlag="2">//@internalRefTargetType</objectPath>
                  <objectUse>Attribute internalRefTargetType - Type of the internal reference target (Chap 3.9.6.1, Table 34)</objectUse>
                  <objectValue valueForm="single" valueAllowed="irtt01" valueTailoring="lexical">Figure </objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt02" valueTailoring="lexical">Table</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt03" valueTailoring="lexical">Multimedia</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt04" valueTailoring="lexical">Supply</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt05" valueTailoring="lexical">Support equipment</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt06" valueTailoring="lexical">Spare</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt07" valueTailoring="lexical">Paragraph</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt08" valueTailoring="lexical">Step</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt09" valueTailoring="lexical">Graphic</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt10" valueTailoring="lexical">Multimedia object</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt11" valueTailoring="lexical">Hotspot</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt12" valueTailoring="lexical">Parameter</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt13" valueTailoring="lexical">Zone</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt14" valueTailoring="lexical">Work location</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt15" valueTailoring="lexical">Service Bulletin material set</objectValue>
                  <objectValue valueForm="single" valueAllowed="irtt16" valueTailoring="lexical">Access point</objectValue>
                  <!-- Values within range irtt51~irtt99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="irtt51~irtt99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00205" />
                  <objectPath allowedObjectFlag="2">//@itemCharacteristic</objectPath>
                  <objectUse>Attribute itemCharacteristic - Item characteristic (Chap 3.9.6.1, Table 35)</objectUse>
                  <!-- "ic01" - Step related to hardness critical process -->
                  <!-- "ic02" - Step related to electrostatic discharge -->
                  <!-- "ic03" - Step with a quality assurance effect -->
                  <!-- Values within range ic51~ic99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="pattern" valueAllowed="((ic0[1-3])|(ic5[1-9])|(ic[6-9][0-9]))((\ )+((ic0[1-3])|(ic5[1-9])|(ic[6-9][0-9])))*"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00206" />
                  <objectPath allowedObjectFlag="2">//@itemOriginator</objectPath>
                  <objectUse>Attribute itemOriginator - Origin of equipment/harness/wire (Chap 3.9.6.1, Table 36)</objectUse>
                  <objectValue valueForm="single" valueAllowed="orig01" valueTailoring="lexical">Manufacturer</objectValue>
                  <objectValue valueForm="single" valueAllowed="orig02" valueTailoring="lexical">Vendor</objectValue>
                  <objectValue valueForm="single" valueAllowed="orig03" valueTailoring="lexical">Partner</objectValue>
                  <!-- Values within range orig51~orig99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="orig51~orig99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00207" />
                  <objectPath allowedObjectFlag="2">//@limitUnitType</objectPath>
                  <objectUse>Attribute limitUnitType - Limit type (Chap 3.9.6.1, Table 37)</objectUse>
                  <objectValue valueForm="single" valueAllowed="lt01" valueTailoring="lexical">Time between overhaul</objectValue>
                  <objectValue valueForm="single" valueAllowed="lt02" valueTailoring="lexical">Hard time</objectValue>
                  <objectValue valueForm="single" valueAllowed="lt03" valueTailoring="lexical">Since last maintenance</objectValue>
                  <objectValue valueForm="single" valueAllowed="lt04" valueTailoring="lexical">Out time limit</objectValue>
                  <objectValue valueForm="single" valueAllowed="lt05" valueTailoring="lexical">On condition</objectValue>
                  <objectValue valueForm="single" valueAllowed="lt06" valueTailoring="lexical">Check maintenance</objectValue>
                  <objectValue valueForm="single" valueAllowed="lt07" valueTailoring="lexical">Functional check</objectValue>
                  <!-- Values within range lt51~lt99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="lt51~lt99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00208" />
                  <objectPath allowedObjectFlag="2">//@listItemPrefix</objectPath>
                  <objectUse>Attribute listItemPrefix - Prefix for list items of random/unordered lists (Chap 3.9.6.1, Table 38)</objectUse>
                  <objectValue valueForm="single" valueAllowed="pf01" valueTailoring="lexical">Simple (No prefix, only indent)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pf02" valueTailoring="lexical">Unorder (Depending on list level, prefix with short dash for first level, bullet for second, and short dash for third level - ISOpub: bull, dash)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pf03" valueTailoring="lexical">Dash (short dash - ISOpub: dash)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pf04" valueTailoring="lexical">Disc (filled circle in circle - ISOamsb: ocir)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pf05" valueTailoring="lexical">Circle (outline - ISOpub: cir)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pf06" valueTailoring="lexical">Square (outline - ISOtech: square)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pf07" valueTailoring="lexical">Bullet (outline - ISOpub: bull)</objectValue>
                  <!-- Values within range pf51~pf99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="pf51~pf99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00209" />
                  <objectPath allowedObjectFlag="2">//@lowestLevel</objectPath>
                  <objectUse>Attribute lowestLevel - Lowest authorized level (Chap 3.9.6.1, Table 39)</objectUse>
                  <objectValue valueForm="single" valueAllowed="la01" valueTailoring="lexical">None</objectValue>
                  <objectValue valueForm="single" valueAllowed="la02" valueTailoring="lexical">Field (Service) level</objectValue>
                  <objectValue valueForm="single" valueAllowed="la03" valueTailoring="lexical">Field/ASB maintenance can remove, replace, and use the item.</objectValue>
                  <objectValue valueForm="single" valueAllowed="la04" valueTailoring="lexical">Below depot sustainment maintenance can remove, replace, and use the item.</objectValue>
                  <objectValue valueForm="single" valueAllowed="la05" valueTailoring="lexical">Specialized repair activity/TASMG can remove, replace, and use the item.</objectValue>
                  <objectValue valueForm="single" valueAllowed="la06" valueTailoring="lexical">Afloat and ashore intermediate maintenance can remove, replace, and use the item.</objectValue>
                  <objectValue valueForm="single" valueAllowed="la07" valueTailoring="lexical">Contractor facility can remove, replace, and use the item.</objectValue>
                  <objectValue valueForm="single" valueAllowed="la08" valueTailoring="lexical">Item is not authorized to be removed, replace, or used at any maintenance level</objectValue>
                  <objectValue valueForm="single" valueAllowed="la09" valueTailoring="lexical">Depot can remove, replace, and use the item.</objectValue>
                  <!-- Values within range la51~la99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="la51~la99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00210" />
                  <objectPath allowedObjectFlag="2">//@maintLevelCode</objectPath>
                  <objectUse>Attribute maintLevelCode - Maintenance Level Code (Chap 3.9.6.1, Table 40)</objectUse>
                  <objectValue valueForm="single" valueAllowed="ml01" valueTailoring="lexical">Level 1</objectValue>
                  <objectValue valueForm="single" valueAllowed="ml02" valueTailoring="lexical">Level 2</objectValue>
                  <objectValue valueForm="single" valueAllowed="ml03" valueTailoring="lexical">Level 3</objectValue>
                  <objectValue valueForm="single" valueAllowed="ml04" valueTailoring="lexical">Level 4</objectValue>
                  <objectValue valueForm="single" valueAllowed="ml05" valueTailoring="lexical">Level 5</objectValue>
                  <!-- Values within range ml51~ml99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="ml51~ml99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00211" />
                  <objectPath allowedObjectFlag="2">//@materialUsage</objectPath>
                  <objectUse>Attribute materialUsage - Material usage (Chap 3.9.6.1, Table 41)</objectUse>
                  <objectValue valueForm="single" valueAllowed="mu01" valueTailoring="lexical">Discarded</objectValue>
                  <objectValue valueForm="single" valueAllowed="mu02" valueTailoring="lexical">Retained</objectValue>
                  <objectValue valueForm="single" valueAllowed="mu03" valueTailoring="lexical">Modified from</objectValue>
                  <objectValue valueForm="single" valueAllowed="mu04" valueTailoring="lexical">Referenced</objectValue>
                  <objectValue valueForm="single" valueAllowed="mu05" valueTailoring="lexical">Material set</objectValue>
                  <objectValue valueForm="single" valueAllowed="mu06" valueTailoring="lexical">Modified to</objectValue>
                  <!-- Values within range mu51~mu99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="mu51~mu99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00256"/>
                  <objectPath allowedObjectFlag="2">//@operationType</objectPath>
                  <objectUse>Attribute operationType - Operation type (Chap 3.9.6.1, Table 42)</objectUse>
                  <objectValue valueForm="single" valueAllowed="op01" valueTailoring="lexical">ETOPS (Extended Twin Operations)</objectValue>
                  <objectValue valueForm="single" valueAllowed="op02" valueTailoring="lexical">RNP (Required Navigation Performance) system</objectValue>
                  <objectValue valueForm="single" valueAllowed="op03" valueTailoring="lexical">OMTS (On-Board Mobile Telephony System)</objectValue>
                  <!-- Values within range op51~op99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="op51~op99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00212" />
                  <objectPath allowedObjectFlag="2">//@partCharacteristic</objectPath>
                  <objectUse>Attribute partCharacteristic - Part characteristic (Chap 3.9.6.1, Table 43)</objectUse>
                  <!-- "pc01" - A hardness critical item -->
                  <!-- "pc02" - Flight safety and critical aircraft part -->
                  <!-- "pc03" - Mandatory replacement part -->
                  <!-- "pc04" - Critical safety item -->
                  <!-- "pc05" - Test equipment -->
                  <!-- "pc06" - Part with electrostatic discharge sensitivity -->
                  <objectValue valueForm="pattern" valueAllowed="((pc0[1-6])|(pc5[1-9])|(pc[6-9][0-9]))((\ )+((pc0[1-6])|(pc5[1-9])|(pc[6-9][0-9])))*"/>
                  <!-- Values within range pc51~pc99 can be allocated and defined by projects or organizations -->
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00213" />
                  <objectPath allowedObjectFlag="2">//@partStatus</objectPath>
                  <objectUse>Attribute partStatus - Status of the part at ISN level (Chap 3.9.6.1, Table 44)</objectUse>
                  <objectValue valueForm="single" valueAllowed="pst01" valueTailoring="lexical">Basic part</objectValue>
                  <objectValue valueForm="single" valueAllowed="pst02" valueTailoring="lexical">Oversize/undersize</objectValue>
                  <objectValue valueForm="single" valueAllowed="pst03" valueTailoring="lexical">Select from</objectValue>
                  <objectValue valueForm="single" valueAllowed="pst04" valueTailoring="lexical">Interchangeable</objectValue>
                  <objectValue valueForm="single" valueAllowed="pst05" valueTailoring="lexical">Alternative</objectValue>
                  <!-- Values within range pst51~pst99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="pst51~pst99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00214" />
                  <objectPath allowedObjectFlag="2">//@partUsageCode</objectPath>
                  <objectUse>Attribute partUsageCode - The part Usage Code (Chap 3.9.6.1, Table 45)</objectUse>
                  <objectValue valueForm="single" valueAllowed="pu01" valueTailoring="lexical">Standard part</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu02" valueTailoring="lexical">Expendable part</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu03" valueTailoring="lexical">Components of end-item parts</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu04" valueTailoring="lexical">Basic issue item parts</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu05" valueTailoring="lexical">Item required to operate equipment</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu06" valueTailoring="lexical">Tool item</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu07" valueTailoring="lexical">Special tool</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu08" valueTailoring="lexical">Standard mechanical hardware items</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu09" valueTailoring="lexical">Hardware</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu10" valueTailoring="lexical">Line replaceable item</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu11" valueTailoring="lexical">Anesthetics/Medical chemicals</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu12" valueTailoring="lexical">Module</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu13" valueTailoring="lexical">Ammunition with dangerous substances</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu14" valueTailoring="lexical">Modification leaflet</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu15" valueTailoring="lexical">Medical supplies</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu16" valueTailoring="lexical">Modification set</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu17" valueTailoring="lexical">None of the other codes applies</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu18" valueTailoring="lexical">Role equipment</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu19" valueTailoring="lexical">Raw materials</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu20" valueTailoring="lexical">Split design module</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu21" valueTailoring="lexical">Software remarks</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu22" valueTailoring="lexical">Part</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu23" valueTailoring="lexical">Basic issue item</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu24" valueTailoring="lexical">Components of end item</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu25" valueTailoring="lexical">Tool</objectValue>
                  <objectValue valueForm="single" valueAllowed="pu26" valueTailoring="lexical">Additional authorization list item</objectValue>
                  <!-- Values within range pu51~pu99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="pu51~pu99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00215" />
                  <objectPath allowedObjectFlag="2">//@pmEntryType</objectPath>
                  <objectUse>Attribute pmEntryType - Publication module entry type (Chap 3.9.6.1, Table 46)</objectUse>
                  <objectValue valueForm="single" valueAllowed="pmt01" valueTailoring="lexical">Title page (TP)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt02" valueTailoring="lexical">Configuration (CONF)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt03" valueTailoring="lexical">Copyright statements (COPY)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt04" valueTailoring="lexical">Administrative and legal statements (ADMIN)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt05" valueTailoring="lexical">Safety statements (SAFE)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt06" valueTailoring="lexical">List of effective data modules (LOEDM)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt07" valueTailoring="lexical">Change record (CR)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt08" valueTailoring="lexical">Highlights (HIGH)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt09" valueTailoring="lexical">List of abbreviations (LOA)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt10" valueTailoring="lexical">List of terms (LOT)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt11" valueTailoring="lexical">List of symbols (LOS)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt12" valueTailoring="lexical">Technical standard record (TSR)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt13" valueTailoring="lexical">Table of contents (TOC)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt14" valueTailoring="lexical">List of illustrations (LOI)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt15" valueTailoring="lexical">List of tables (LOTBL)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt16" valueTailoring="lexical">List of applicable specifications and documentation (LOASD)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt17" valueTailoring="lexical">List of suppliers (LOSUP)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt18" valueTailoring="lexical">List of support equipment (LOSE)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt19" valueTailoring="lexical">List of supplies (LOSU)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt20" valueTailoring="lexical">List of spares (LOSP)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt21" valueTailoring="lexical">Introduction (INTRO)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt22" valueTailoring="lexical">Description of function (FUNC)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt23" valueTailoring="lexical">Technical description (DESC)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt24" valueTailoring="lexical">Diagrams and schematics (SCHEM)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt25" valueTailoring="lexical">Maintenance planning (MAINT)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt26" valueTailoring="lexical">Removal and installation (RI)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt27" valueTailoring="lexical">Task sets (TS)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt28" valueTailoring="lexical">Servicing (SERVC)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt29" valueTailoring="lexical">Examination, test, checks, and fault isolation (TEST)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt30" valueTailoring="lexical">Disassemble (DIS)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt31" valueTailoring="lexical">Repair (REP)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt32" valueTailoring="lexical">Assemble (ASSY)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt33" valueTailoring="lexical">Storage (STORE)</objectValue>
                  <objectValue valueForm="single" valueAllowed="pmt34" valueTailoring="lexical">Illustrated Parts Data (IPD)</objectValue>
                  <!-- Values within range pmt51~pmt99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="pmt51~pmt99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00257"/>
                  <objectPath allowedObjectFlag="2">//@pmType</objectPath>
                  <objectUse>Attribute pmType - Type of publication (Chap 3.9.6.1, Table 47)</objectUse>
                  <objectValue valueForm="single" valueAllowed="pt01" valueTailoring="lexical">Component Maintenance Publication</objectValue>
                  <objectValue valueForm="single" valueAllowed="pt02" valueTailoring="lexical">Illustrated Parts Data</objectValue>
                  <objectValue valueForm="single" valueAllowed="pt03" valueTailoring="lexical">Service Bulletin</objectValue>
                  <!-- Values within range pt51~pt99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="pt51~pt99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00216" />
                  <objectPath allowedObjectFlag="2">//@productCategory</objectPath>
                  <objectUse>Attribute productCategory - Product categories (Chap 3.9.6.1, Table 48)</objectUse>
                  <objectValue valueForm="single" valueAllowed="pcg01" valueTailoring="lexical">Adhesives sealant</objectValue>
                  <objectValue valueForm="single" valueAllowed="pcg04" valueTailoring="lexical">Anti freeze and de-icing products</objectValue>
                  <objectValue valueForm="single" valueAllowed="pcg08" valueTailoring="lexical">Biocide products</objectValue>
                  <objectValue valueForm="single" valueAllowed="pcg09" valueTailoring="lexical">Coating and paints, fillers, putties, thinners</objectValue>
                  <objectValue valueForm="single" valueAllowed="pcg13" valueTailoring="lexical">Fuels</objectValue>
                  <objectValue valueForm="single" valueAllowed="pcg14" valueTailoring="lexical">Metal surface treatment products</objectValue>
                  <objectValue valueForm="single" valueAllowed="pcg15" valueTailoring="lexical">Non-metal surface treatment products</objectValue>
                  <objectValue valueForm="single" valueAllowed="pcg16" valueTailoring="lexical">Heat transfers fluids</objectValue>
                  <objectValue valueForm="single" valueAllowed="pcg17" valueTailoring="lexical">Hydraulic fluids</objectValue>
                  <objectValue valueForm="single" valueAllowed="pcg24" valueTailoring="lexical">Lubricants, grease, release products</objectValue>
                  <objectValue valueForm="single" valueAllowed="pcg32" valueTailoring="lexical">Polymer preparation and compounds</objectValue>
                  <objectValue valueForm="single" valueAllowed="pcg35" valueTailoring="lexical">Washing and cleaning product</objectValue>
                  <!-- Values within range pcg51~pcg99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="pcg51~pcg99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00217" />
                  <objectPath allowedObjectFlag="2">//@productItemType</objectPath>
                  <objectUse>Attribute productItemType - Type of product item (Chap 3.9.6.1, Table 49)</objectUse>
                  <objectValue valueForm="single" valueAllowed="pi01" valueTailoring="lexical">Frame</objectValue>
                  <objectValue valueForm="single" valueAllowed="pi02" valueTailoring="lexical">Rib</objectValue>
                  <objectValue valueForm="single" valueAllowed="pi03" valueTailoring="lexical">Stringer</objectValue>
                  <!-- Values within range pi51~pi99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="pi51~pi99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00218" />
                  <objectPath allowedObjectFlag="2">//@quantityType</objectPath>
                  <objectUse>Attribute quantityType - Quantity data type (Chap 3.9.6.1, Table 50)</objectUse>
                  <objectValue valueForm="single" valueAllowed="qty01" valueTailoring="lexical">Length</objectValue>
                  <objectValue valueForm="single" valueAllowed="qty02" valueTailoring="lexical">Price</objectValue>
                  <objectValue valueForm="single" valueAllowed="qty03" valueTailoring="lexical">Temperature</objectValue>
                  <objectValue valueForm="single" valueAllowed="qty04" valueTailoring="lexical">Time</objectValue>
                  <objectValue valueForm="single" valueAllowed="qty05" valueTailoring="lexical">Torque value</objectValue>
                  <objectValue valueForm="single" valueAllowed="qty06" valueTailoring="lexical">Voltage</objectValue>
                  <objectValue valueForm="single" valueAllowed="qty07" valueTailoring="lexical">Volume</objectValue>
                  <objectValue valueForm="single" valueAllowed="qty08" valueTailoring="lexical">Mass</objectValue>
                  <!-- Values within range qty51~qty99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="qty51~qty99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00219" />
                  <objectPath allowedObjectFlag="2">//@refType</objectPath>
                  <objectUse>Attribute refType - Reference to part type (Chap 3.9.6.1, Table 51)</objectUse>
                  <objectValue valueForm="single" valueAllowed="rft01" valueTailoring="lexical">Refer to next higher assembly</objectValue>
                  <objectValue valueForm="single" valueAllowed="rft02" valueTailoring="lexical">Refer to detail(s)</objectValue>
                  <objectValue valueForm="single" valueAllowed="rft03" valueTailoring="lexical">Equivalent part(s)</objectValue>
                  <objectValue valueForm="single" valueAllowed="rft04" valueTailoring="lexical">Substitute part(s)</objectValue>
                  <objectValue valueForm="single" valueAllowed="rft05" valueTailoring="lexical">Attaching part(s)</objectValue>
                  <objectValue valueForm="single" valueAllowed="rft06" valueTailoring="lexical">Removal/Installation part(s)</objectValue>
                  <objectValue valueForm="single" valueAllowed="rft07" valueTailoring="lexical">Select from part(s)</objectValue>
                  <objectValue valueForm="single" valueAllowed="rft08" valueTailoring="lexical">Oversize/Undersize</objectValue>
                  <objectValue valueForm="single" valueAllowed="rft09" valueTailoring="lexical">Connecting item(s)</objectValue>
                  <objectValue valueForm="single" valueAllowed="rft10" valueTailoring="lexical">Breakdown</objectValue>
                  <!-- Values within range rft51~rft99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="rft51~rft99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00220" />
                  <objectPath allowedObjectFlag="2">//@reqCondCategory</objectPath>
                  <objectUse>Attribute reqCondCategory - Required condition category (Chap 3.9.6.1, Table 52)</objectUse>
                  <objectValue valueForm="single" valueAllowed="rcc01" valueTailoring="lexical">Normal</objectValue>
                  <objectValue valueForm="single" valueAllowed="rcc02" valueTailoring="lexical">Special environmental conditions such as reduced lighting, ventilation, and temperature.</objectValue>
                  <objectValue valueForm="single" valueAllowed="rcc03" valueTailoring="lexical">Jacked</objectValue>
                  <objectValue valueForm="single" valueAllowed="rcc04" valueTailoring="lexical">Electric power</objectValue>
                  <objectValue valueForm="single" valueAllowed="rcc05" valueTailoring="lexical">Pneumatic power</objectValue>
                  <objectValue valueForm="single" valueAllowed="rcc06" valueTailoring="lexical">Hydraulic power</objectValue>
                  <!-- Values within range rcc51~rcc99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="rcc51~rcc99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00221" />
                  <objectPath allowedObjectFlag="2">//@reqTechInfoCategory</objectPath>
                  <objectUse>Attribute reqTechInfoCategory - Required technical information category (Chap 3.9.6.1, Table 53)</objectUse>
                  <objectValue valueForm="single" valueAllowed="ti01" valueTailoring="lexical">Publication module (PM)</objectValue>
                  <objectValue valueForm="single" valueAllowed="ti02" valueTailoring="lexical">Data module (DM)</objectValue>
                  <objectValue valueForm="single" valueAllowed="ti03" valueTailoring="lexical">Drawing</objectValue>
                  <objectValue valueForm="single" valueAllowed="ti04" valueTailoring="lexical">Electrical diagram</objectValue>
                  <objectValue valueForm="single" valueAllowed="ti05" valueTailoring="lexical">Schematic diagram</objectValue>
                  <objectValue valueForm="single" valueAllowed="ti06" valueTailoring="lexical">Safety sheet</objectValue>
                  <!-- Values within range ti51~ti99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="ti51~ti99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00222" />
                  <objectPath allowedObjectFlag="2">//@resetCaption</objectPath>
                  <objectUse>Attribute resetCaption - Caption for dialog reset caption (Chap 3.9.6.1, Table 54)</objectUse>
                  <objectValue valueForm="single" valueAllowed="re01" valueTailoring="lexical">Sets the caption to RESET</objectValue>
                  <objectValue valueForm="single" valueAllowed="re02" valueTailoring="lexical">Sets the caption to CLEAR</objectValue>
                  <!-- Values within range re51~re99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="re51~re99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00223" />
                  <objectPath allowedObjectFlag="2">//@responseType</objectPath>
                  <objectUse>Attribute responseType - Type of response to a comment (Chap 3.9.6.1, Table 55)</objectUse>
                  <objectValue valueForm="single" valueAllowed="rt01" valueTailoring="lexical">Accepted</objectValue>
                  <objectValue valueForm="single" valueAllowed="rt02" valueTailoring="lexical">Pending</objectValue>
                  <objectValue valueForm="single" valueAllowed="rt03" valueTailoring="lexical">Partly accepted</objectValue>
                  <objectValue valueForm="single" valueAllowed="rt04" valueTailoring="lexical">Rejected</objectValue>
                  <!-- Values within range rt51~rt99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="rt51~rt99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00224" />
                  <objectPath allowedObjectFlag="2">//@sbComplianceCategory</objectPath>
                  <objectUse>Attribute sbComplianceCategory - SB compliance category (Chap 3.9.6.1, Table 56)</objectUse>
                  <objectValue valueForm="single" valueAllowed="sbcc01" valueTailoring="lexical">Mandatory (Service Bulletin must be accomplished)</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbcc02" valueTailoring="lexical">Recommended (Service Bulletin recommended to be accomplished to prevent significant operational disruptions)</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbcc03" valueTailoring="lexical">Desirable (Service Bulletin to introduce improvements)</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbcc04" valueTailoring="lexical">Optional (Service Bulletin for convenience or option)</objectValue>
                  <!-- Values within range sbcc51~sbcc99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="sbcc51~sbcc99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00225" />
                  <objectPath allowedObjectFlag="2">//@sbImpactType</objectPath>
                  <objectUse>Attribute sbImpactType - SB impact type (Chap 3.9.6.1, Table 57)</objectUse>
                  <objectValue valueForm="single" valueAllowed="sbit01" valueTailoring="lexical">Weight</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbit02" valueTailoring="lexical">Balance</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbit03" valueTailoring="lexical">Direct current electrical load</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbit04" valueTailoring="lexical">Alternating current electrical load</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbit05" valueTailoring="lexical">Maintenance publications</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbit06" valueTailoring="lexical">Operational publications</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbit07" valueTailoring="lexical">Electrical Structure Network</objectValue>
                  <!-- Values within range sbit51~sbit99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="sbit51~sbit99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00226" />
                  <objectPath allowedObjectFlag="2">//@sbMaterialType</objectPath>
                  <objectUse>Attribute sbMaterialType - SB material type (Chap 3.9.6.1, Table 58)</objectUse>
                  <objectValue valueForm="single" valueAllowed="sbmt01" valueTailoring="lexical">Set of material or individual material specific to the Service Bulletin</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbmt02" valueTailoring="lexical">Set of material or individual material not specially built for the Service Bulletin</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbmt03" valueTailoring="lexical">Set of references to set of material or individual material or external material</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbmt04" valueTailoring="lexical">Set of hazardous removed material</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbmt05" valueTailoring="lexical">Set of re-identified material</objectValue>
                  <!-- Values within range sbmt51~sbmt99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="sbmt51~sbmt99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00227" />
                  <objectPath allowedObjectFlag="2">//@sbTaskCategory</objectPath>
                  <objectUse>Attribute sbTaskCategory - Category of the SB task (Chap 3.9.6.1, Table 59)</objectUse>
                  <objectValue valueForm="single" valueAllowed="sbtc01" valueTailoring="lexical">Modification</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtc02" valueTailoring="lexical">Inspection</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtc03" valueTailoring="lexical">Repair</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtc04" valueTailoring="lexical">Evaluation</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtc05" valueTailoring="lexical">Administrative</objectValue>
                  <!-- Values within range sbtc51~sbtc99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="sbtc51~sbtc99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00228" />
                  <objectPath allowedObjectFlag="2">//@sbTimeComplianceType</objectPath>
                  <objectUse>Attribute sbTimeComplianceType - SB time compliance type (Chap 3.9.6.1, Table 60)</objectUse>
                  <objectValue valueForm="single" valueAllowed="sbtct01" valueTailoring="lexical">Basic limit</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtct02" valueTailoring="lexical">Grace period</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtct03" valueTailoring="lexical">Repetitive interval</objectValue>
                  <!-- Values within range sbtct51~sbtct99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="sbtct51~sbtct99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00229" />
                  <objectPath allowedObjectFlag="2">//@sbTopicType</objectPath>
                  <objectUse>Attribute sbTopicType - SB topic type (Chap 3.9.6.1, Table 61)</objectUse>
                  <objectValue valueForm="single" valueAllowed="sbtt01" valueTailoring="lexical">Revision information</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt02" valueTailoring="lexical">Summary</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt03" valueTailoring="lexical">Planning information</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt04" valueTailoring="lexical">Additional information</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt05" valueTailoring="lexical">Applicability</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt06" valueTailoring="lexical">Concurrent requirements</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt07" valueTailoring="lexical">Reason</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt08" valueTailoring="lexical">Description</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt09" valueTailoring="lexical">Compliance</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt10" valueTailoring="lexical">Approval</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt11" valueTailoring="lexical">Manpower</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt12" valueTailoring="lexical">Weight and balance</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt13" valueTailoring="lexical">Electrical load data</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt14" valueTailoring="lexical">Software accomplishment summary</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt15" valueTailoring="lexical">Referenced documentation</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt16" valueTailoring="lexical">Documentation affected</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt17" valueTailoring="lexical">Industry support information</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt18" valueTailoring="lexical">Material information</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt19" valueTailoring="lexical">General evaluation</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt20" valueTailoring="lexical">General illustration</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt21" valueTailoring="lexical">Additional work</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt22" valueTailoring="lexical">Revision reason</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt23" valueTailoring="lexical">Revision history</objectValue>
                  <objectValue valueForm="single" valueAllowed="sbtt24" valueTailoring="lexical">Revision sequence</objectValue>
                  <!-- Values within range sbtt51~sbtt99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="sbtt51~sbtt99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00230" />
                  <objectPath allowedObjectFlag="2">//@scoEntryType</objectPath>
                  <objectUse>Attribute scoEntryType - Type of SCO entry (Chap 3.9.6.1, Table 62)</objectUse>
                  <objectValue valueForm="single" valueAllowed="scot01" valueTailoring="lexical">IMS/SCORM Manifest SCO type resource</objectValue>
                  <objectValue valueForm="single" valueAllowed="scot02" valueTailoring="lexical">IMS/SCORM Manifest asset type resource</objectValue>
                  <!-- Values within range scot51~scot99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="scot51~scot99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00231" />
                  <objectPath allowedObjectFlag="2">//@securityClassification</objectPath>
                  <objectUse>Attribute securityClassification - Security classification (Chap 3.9.6.1, Table 63)</objectUse>
                  <objectValue valueForm="single" valueAllowed="01"/>
                  <objectValue valueForm="single" valueAllowed="02"/>
                  <objectValue valueForm="single" valueAllowed="03"/>
                  <objectValue valueForm="single" valueAllowed="04"/>
                  <objectValue valueForm="single" valueAllowed="05"/>
                  <objectValue valueForm="single" valueAllowed="06"/>
                  <objectValue valueForm="single" valueAllowed="07"/>
                  <objectValue valueForm="single" valueAllowed="08"/>
                  <objectValue valueForm="single" valueAllowed="09"/>
                  <!-- Values within range 51~99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="51~99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00232" />
                  <objectPath allowedObjectFlag="2">//@significantParaDataType</objectPath>
                  <objectUse>Attribute significantParaDataType - Paragraph significant data type (Chap 3.9.6.1, Table 64)</objectUse>
                  <objectValue valueForm="single" valueAllowed="psd01" valueTailoring="lexical">Ammunition</objectValue>
                  <objectValue valueForm="single" valueAllowed="psd02" valueTailoring="lexical">Instruction disposition</objectValue>
                  <objectValue valueForm="single" valueAllowed="psd03" valueTailoring="lexical">Lubricant</objectValue>
                  <objectValue valueForm="single" valueAllowed="psd04" valueTailoring="lexical">Maintenance level</objectValue>
                  <objectValue valueForm="single" valueAllowed="psd05" valueTailoring="lexical">Manufacturer code</objectValue>
                  <objectValue valueForm="single" valueAllowed="psd06" valueTailoring="lexical">Manufacturers recommendation</objectValue>
                  <objectValue valueForm="single" valueAllowed="psd07" valueTailoring="lexical">Modification code</objectValue>
                  <objectValue valueForm="single" valueAllowed="psd08" valueTailoring="lexical">Qualification code</objectValue>
                  <objectValue valueForm="single" valueAllowed="psd09" valueTailoring="lexical">Training level</objectValue>
                  <objectValue valueForm="single" valueAllowed="psd10" valueTailoring="lexical">Control or Indicator value</objectValue>
                  <!-- Values within range psd51~psd99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="psd51~psd99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00233" />
                  <objectPath allowedObjectFlag="2">//@skillLevelCode</objectPath>
                  <objectUse>Attribute skillLevelCode - Personnel skill level (Chap 3.9.6.1, Table 65)</objectUse>
                  <objectValue valueForm="single" valueAllowed="sk01" valueTailoring="lexical">Basic</objectValue>
                  <objectValue valueForm="single" valueAllowed="sk02" valueTailoring="lexical">Intermediate</objectValue>
                  <objectValue valueForm="single" valueAllowed="sk03" valueTailoring="lexical">Advanced</objectValue>
                  <!-- Values within range sk51~sk99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="sk51~sk99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00234" />
                  <objectPath allowedObjectFlag="2">//@skillType</objectPath>
                  <objectUse>Attribute skillType - Personnel skill category (Chap 3.9.6.1, Table 66)</objectUse>
                  <objectValue valueForm="single" valueAllowed="st01" valueTailoring="lexical">Airframe (AIRPL)</objectValue>
                  <objectValue valueForm="single" valueAllowed="st02" valueTailoring="lexical">Electrical (ELEC)</objectValue>
                  <objectValue valueForm="single" valueAllowed="st03" valueTailoring="lexical">Avionic (AVION)</objectValue>
                  <objectValue valueForm="single" valueAllowed="st04" valueTailoring="lexical">Engine (ENGIN)</objectValue>
                  <!-- Values within range st51~st99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="st51~st99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00258"/>
                  <objectPath allowedObjectFlag="2">//@softwareClassificationValue</objectPath>
                  <objectUse>Attribute softwareClassificationValue - Software classification (Chap 3.9.6.1, Table 67)</objectUse>
                  <objectValue valueForm="single" valueAllowed="scv01" valueTailoring="lexical">Loadable Software Aircraft Part (LSAP)</objectValue>
                  <objectValue valueForm="single" valueAllowed="scv02" valueTailoring="lexical">Aeronautical Database (ADB)</objectValue>
                  <objectValue valueForm="single" valueAllowed="scv03" valueTailoring="lexical"> Technical Publication Software</objectValue>
                  <objectValue valueForm="single" valueAllowed="scv04" valueTailoring="lexical">Maintenance Operation Software (MOS)</objectValue>
                  <objectValue valueForm="single" valueAllowed="scv05" valueTailoring="lexical"> Flight Operation Software (FOS)</objectValue>
                  <!-- Values within range scv51~scv99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="scv51~scv99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00259"/>
                  <objectPath allowedObjectFlag="2">//@softwareCustomizationStatusValue</objectPath>
                  <objectUse>Attribute softwareCustomizationStatusValue - Software customization status (Chap 3.9.6.1, Table 68)</objectUse>
                  <objectValue valueForm="single" valueAllowed="scs01" valueTailoring="lexical">Software customization mandatory</objectValue>
                  <objectValue valueForm="single" valueAllowed="scs02" valueTailoring="lexical">Software customization allowed</objectValue>
                  <objectValue valueForm="single" valueAllowed="scs03" valueTailoring="lexical">Software customization not allowed</objectValue>
                  <!-- Values within range scs51~scs99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="scs51~scs99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00235" />
                  <objectPath allowedObjectFlag="2">//@sourceCriticality</objectPath>
                  <objectUse>Attribute sourceCriticality - Source criticality (Chap 3.9.6.1, Table 69)</objectUse>
                  <!-- Values within range sc51~sc99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="sc51~sc99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00236" />
                  <objectPath allowedObjectFlag="2">//@sourceTypeCode</objectPath>
                  <objectUse>Attribute sourceTypeCode - Source type code (Chap 3.9.6.1, Table 70)</objectUse>
                  <!-- Values within range stc51~stc99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="stc51~stc99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00260"/>
                  <objectPath allowedObjectFlag="2">//@sourcingTypeValue</objectPath>
                  <objectUse>Attribute sourcingTypeValue - Part sourcing type (Chap 3.9.6.1, Table 71)</objectUse>
                  <objectValue valueForm="single" valueAllowed="stv01" valueTailoring="lexical">BFE (Buyer Furnished Equipment) part</objectValue>
                  <objectValue valueForm="single" valueAllowed="stv02" valueTailoring="lexical">SFE (Seller Furnished Equipment) part</objectValue>
                  <!-- Values within range stv51~stv99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="stv51~stv99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00237" />
                  <objectPath allowedObjectFlag="2">//@submitCaption</objectPath>
                  <objectUse>Attribute submitCaption - Caption for dialog submit function (Chap 3.9.6.1, Table 72)</objectUse>
                  <objectValue valueForm="single" valueAllowed="ok01" valueTailoring="lexical">Sets the caption to OK</objectValue>
                  <objectValue valueForm="single" valueAllowed="ok02" valueTailoring="lexical">Sets the caption to SUBMIT</objectValue>
                  <objectValue valueForm="single" valueAllowed="ok04" valueTailoring="lexical">Sets the caption to CONTINUE</objectValue>
                  <objectValue valueForm="single" valueAllowed="ok05" valueTailoring="lexical">Sets the caption to EXIT</objectValue>
                  <!-- Values within range ok51~ok99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="ok51~ok99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00238" />
                  <objectPath allowedObjectFlag="2">//@supervisorLevelCode</objectPath>
                  <objectUse>Attribute supervisorLevelCode - Supervisor level (Chap 3.9.6.1, Table 73)</objectUse>
                  <objectValue valueForm="single" valueAllowed="sl01" valueTailoring="lexical">Low</objectValue>
                  <objectValue valueForm="single" valueAllowed="sl02" valueTailoring="lexical">Low intermediate</objectValue>
                  <objectValue valueForm="single" valueAllowed="sl03" valueTailoring="lexical">High intermediate</objectValue>
                  <objectValue valueForm="single" valueAllowed="sl04" valueTailoring="lexical">High</objectValue>
                  <!-- Values within range sl51~sl99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="sl51~sl99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00239" />
                  <objectPath allowedObjectFlag="2">//@supplyNumberType</objectPath>
                  <objectUse>Attribute supplyNumberType - Type of supply (Chap 3.9.6.1, Table 74)</objectUse>
                  <objectValue valueForm="single" valueAllowed="sp01" valueTailoring="lexical">Commercial reference</objectValue>
                  <objectValue valueForm="single" valueAllowed="sp02" valueTailoring="lexical">Specification</objectValue>
                  <objectValue valueForm="single" valueAllowed="sp03" valueTailoring="lexical">Mixture</objectValue>
                  <objectValue valueForm="single" valueAllowed="sp04" valueTailoring="lexical">Set</objectValue>
                  <objectValue valueForm="single" valueAllowed="sp05" valueTailoring="lexical">Article</objectValue>
                  <!-- Values within range sp51~sp99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="sp51~sp99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00240" />
                  <objectPath allowedObjectFlag="2">//@taskCode</objectPath>
                  <objectUse>Attribute taskCode - Task code (Chap 3.9.6.1, Table 75)</objectUse>
                  <objectValue valueForm="single" valueAllowed="taskcd01" valueTailoring="lexical">Detailed inspection (DET)</objectValue>
                  <objectValue valueForm="single" valueAllowed="taskcd02" valueTailoring="lexical">Discard (DIS)</objectValue>
                  <objectValue valueForm="single" valueAllowed="taskcd03" valueTailoring="lexical">Functional Check (FNC)</objectValue>
                  <objectValue valueForm="single" valueAllowed="taskcd04" valueTailoring="lexical">General visual inspection (GVI)</objectValue>
                  <objectValue valueForm="single" valueAllowed="taskcd05" valueTailoring="lexical">Lubrication (LUB)</objectValue>
                  <objectValue valueForm="single" valueAllowed="taskcd06" valueTailoring="lexical">Operational check (OPC)</objectValue>
                  <objectValue valueForm="single" valueAllowed="taskcd07" valueTailoring="lexical">Restoration (RST)</objectValue>
                  <objectValue valueForm="single" valueAllowed="taskcd08" valueTailoring="lexical">Servicing (SVC)</objectValue>
                  <objectValue valueForm="single" valueAllowed="taskcd09" valueTailoring="lexical">Visual check (VCK)</objectValue>
                  <objectValue valueForm="single" valueAllowed="taskcd10" valueTailoring="lexical">Special detailed inspection (SDI)</objectValue>
                  <!-- Values within range taskcd51~taskcd99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="taskcd51~taskcd99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00241" />
                  <objectPath allowedObjectFlag="2">//@thresholdUnitOfMeasure</objectPath>
                  <objectUse>Attribute thresholdUnitOfMeasure - Unit of measurement for the threshold interval (Chap 3.9.6.1, Table 76)</objectUse>
                  <objectValue valueForm="single" valueAllowed="th01" valueTailoring="lexical">Flight hours</objectValue>
                  <objectValue valueForm="single" valueAllowed="th02" valueTailoring="lexical">Flight cycles</objectValue>
                  <objectValue valueForm="single" valueAllowed="th03" valueTailoring="lexical">Months</objectValue>
                  <objectValue valueForm="single" valueAllowed="th04" valueTailoring="lexical">Weeks</objectValue>
                  <objectValue valueForm="single" valueAllowed="th05" valueTailoring="lexical">Years</objectValue>
                  <objectValue valueForm="single" valueAllowed="th06" valueTailoring="lexical">Days</objectValue>
                  <objectValue valueForm="single" valueAllowed="th07" valueTailoring="lexical">Supersonic cycles</objectValue>
                  <objectValue valueForm="single" valueAllowed="th08" valueTailoring="lexical">Pressure cycles</objectValue>
                  <objectValue valueForm="single" valueAllowed="th09" valueTailoring="lexical">Engine cycles</objectValue>
                  <objectValue valueForm="single" valueAllowed="th10" valueTailoring="lexical">Engine change</objectValue>
                  <objectValue valueForm="single" valueAllowed="th11" valueTailoring="lexical">Shop visits</objectValue>
                  <objectValue valueForm="single" valueAllowed="th12" valueTailoring="lexical">Auxiliary power unit change</objectValue>
                  <objectValue valueForm="single" valueAllowed="th13" valueTailoring="lexical">Landing gear change</objectValue>
                  <objectValue valueForm="single" valueAllowed="th14" valueTailoring="lexical">Wheel change</objectValue>
                  <objectValue valueForm="single" valueAllowed="th15" valueTailoring="lexical">Engine start</objectValue>
                  <objectValue valueForm="single" valueAllowed="th16" valueTailoring="lexical">APU hours</objectValue>
                  <objectValue valueForm="single" valueAllowed="th17" valueTailoring="lexical">Engine hours</objectValue>
                  <objectValue valueForm="single" valueAllowed="th18" valueTailoring="lexical">Elapsed hours</objectValue>
                  <objectValue valueForm="single" valueAllowed="th19" valueTailoring="lexical">Landings</objectValue>
                  <objectValue valueForm="single" valueAllowed="th20" valueTailoring="lexical">Operating cycles</objectValue>
                  <objectValue valueForm="single" valueAllowed="th21" valueTailoring="lexical">Operating hours</objectValue>
                  <objectValue valueForm="single" valueAllowed="th22" valueTailoring="lexical">Supersonic hours</objectValue>
                  <objectValue valueForm="single" valueAllowed="th23" valueTailoring="lexical">A check</objectValue>
                  <objectValue valueForm="single" valueAllowed="th24" valueTailoring="lexical">B check</objectValue>
                  <objectValue valueForm="single" valueAllowed="th25" valueTailoring="lexical">C check</objectValue>
                  <objectValue valueForm="single" valueAllowed="th26" valueTailoring="lexical">D check</objectValue>
                  <objectValue valueForm="single" valueAllowed="th27" valueTailoring="lexical">Daily</objectValue>
                  <objectValue valueForm="single" valueAllowed="th28" valueTailoring="lexical">E check</objectValue>
                  <objectValue valueForm="single" valueAllowed="th29" valueTailoring="lexical">Overnight</objectValue>
                  <objectValue valueForm="single" valueAllowed="th30" valueTailoring="lexical">Preflight</objectValue>
                  <objectValue valueForm="single" valueAllowed="th31" valueTailoring="lexical">Routine check</objectValue>
                  <objectValue valueForm="single" valueAllowed="th32" valueTailoring="lexical">Structural C check</objectValue>
                  <objectValue valueForm="single" valueAllowed="th33" valueTailoring="lexical">Service check</objectValue>
                  <objectValue valueForm="single" valueAllowed="th34" valueTailoring="lexical">Transit</objectValue>
                  <objectValue valueForm="single" valueAllowed="th35" valueTailoring="lexical">Kilometer</objectValue>
                  <objectValue valueForm="single" valueAllowed="th36" valueTailoring="lexical">Consumption in cubic meter</objectValue>
                  <objectValue valueForm="single" valueAllowed="th37" valueTailoring="lexical">Consumption in liter</objectValue>
                  <objectValue valueForm="single" valueAllowed="th38" valueTailoring="lexical">Number of shots - each</objectValue>
                  <objectValue valueForm="single" valueAllowed="th39" valueTailoring="lexical">Number of shots - equivalent full charge (EFC)</objectValue>
                  <!-- Values within range th51~th99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="th51~th99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00242" />
                  <objectPath allowedObjectFlag="2">//@updateReasonType</objectPath>
                  <objectUse>Attribute updateReasonType - Update reason type for reason for update (Chap 3.9.6.1, Table 77)</objectUse>
                  <objectValue valueForm="single" valueAllowed="urt01" valueTailoring="lexical">Editorial change (authored/technical content changed, but technically changes are deemed insignificant)</objectValue>
                  <objectValue valueForm="single" valueAllowed="urt02" valueTailoring="lexical">Technical change (authored/technical content has changed, changes are significant and should be reviewed)</objectValue>
                  <objectValue valueForm="single" valueAllowed="urt03" valueTailoring="lexical">Markup change (changes are solely related to XML markup)</objectValue>
                  <objectValue valueForm="single" valueAllowed="urt04" valueTailoring="lexical">Applicability change (only the applicability has changed)</objectValue>
                  <objectValue valueForm="single" valueAllowed="urt05" valueTailoring="lexical">Unique identifier of the referencing structure has changed</objectValue>
                  <!-- Values within range urt51~urt99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="urt51~urt99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00243" />
                  <objectPath allowedObjectFlag="2">//@verbatimStyle</objectPath>
                  <objectUse>Attribute verbatimStyle - Verbatim style (Chap 3.9.6.1, Table 78)</objectUse>
                  <objectValue valueForm="single" valueAllowed="vs01" valueTailoring="lexical">Generic verbatim</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs02" valueTailoring="lexical">Filename</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs11" valueTailoring="lexical">XML/SGML markup</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs12" valueTailoring="lexical">XML/SGML element name</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs13" valueTailoring="lexical">XML/SGML attribute name</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs14" valueTailoring="lexical">XML/SGML attribute value</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs15" valueTailoring="lexical">XML/SGML entity name</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs16" valueTailoring="lexical">XML/SGML processing instruction</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs21" valueTailoring="lexical">Program prompt</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs22" valueTailoring="lexical">User input</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs23" valueTailoring="lexical">Computer output</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs24" valueTailoring="lexical">Program listing</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs25" valueTailoring="lexical">Program variable name</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs26" valueTailoring="lexical">Program variable value</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs27" valueTailoring="lexical">Constant</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs28" valueTailoring="lexical">Class name</objectValue>
                  <objectValue valueForm="single" valueAllowed="vs29" valueTailoring="lexical">Parameter name</objectValue>
                  <!-- Values within range vs51~vs99 can be allocated and defined by projects or organizations -->
                  <objectValue valueForm="range" valueAllowed="vs51~vs99" valueTailoring="restrictable"/>
                </structureObjectRule>
                <!-- 3.9.6.2 -->
                <structureObjectRule>
                  <brDecisionRef brDecisionIdentNumber="BREX-S1-00244" />
                  <objectPath allowedObjectFlag="2">//@quantityUnitOfMeasure</objectPath>
                  <objectUse>Attribute quantityUnitOfMeasure - Quantity data unit of measure. Apart from the pre-defined fixed values, values within range um51~um99 can be allocated and defined by projects or organizations (Chap 3.9.6.2, Table 2)</objectUse>
                </structureObjectRule>
              </structureObjectRuleGroup>
              <notationRuleList>
                <notationRule>
                  <notationName allowedNotationFlag="0">jpg</notationName>
                </notationRule>
              </notationRuleList>
            </contextRules>
            <nonContextRules>
              <nonContextRule>
                <brDecisionRef brDecisionIdentNumber="BREX-S1-00245" />
                <simplePara>Deletion of data modules is treated as a special case of update. The data module itself is not physically deleted from the CSDB but [only] marked as deleted. (Chap 3.9.5.1, Para 2.2)</simplePara>
              </nonContextRule>
            </nonContextRules>
          </brex>
        </content>
    EOD;
  }
}
