#本项目为SaaSAPIV3 php版demo
##1、esign文件夹下为封装的核心HTTP包
##2、run文件夹下为具体方法运行示例，其中：
###  i、moduleDemo文件夹为常规模块接口，分为认证授权(auth)、成员(members)、印章(seal)、文件&模板(fileAndTemplate)、签署服务(sign)等模块。
###  ii、sceneDemo文件夹为场景化接口，结合常见客户业务流程梳理编写。比如：
####平台方自动+个人用户手动签署流程 （platformToC.php）
####平台方自动+企业用户手动签署流程 （platformToB.php）
####平台方自动+企业用户自动签署流程 （platformToAutoB.php）
####回调通知接收场景（callback.php）




